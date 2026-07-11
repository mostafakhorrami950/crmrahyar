<?php
namespace Shared\Services;

use Shared\Core\Database;
use Shared\Core\Logger;
use Shared\Interfaces\QueueInterface;

class QueueService implements QueueInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function dispatch($job, string $priority = self::PRIORITY_DEFAULT, int $delaySeconds = 0): int
    {
        $jobClass = is_string($job) ? $job : get_class($job);
        $payload = is_object($job) ? json_encode($job) : '{}';
        $availableAt = date('Y-m-d H:i:s', time() + $delaySeconds);

        return $this->db->insert('site_queue_jobs', [
            'queue_name' => $priority,
            'job_class' => $jobClass,
            'payload' => $payload,
            'status' => 'pending',
            'priority' => $priority,
            'available_at' => $availableAt,
            'max_attempts' => 3,
        ]);
    }

    public function process(?string $priority = null, int $limit = 10): int
    {
        $where = "status = 'pending' AND available_at <= NOW()";
        $params = [];
        if ($priority) {
            $where .= " AND priority = :pri";
            $params[':pri'] = $priority;
        }

        $jobs = $this->db->fetchAll(
            "SELECT * FROM site_queue_jobs WHERE {$where} ORDER BY priority DESC, available_at ASC LIMIT {$limit}",
            $params
        );

        $processed = 0;
        foreach ($jobs as $job) {
            try {
                $this->db->update('site_queue_jobs', [
                    'status' => 'processing',
                    'started_at' => date('Y-m-d H:i:s'),
                    'attempts' => $job->attempts + 1,
                ], 'id = :id', [':id' => $job->id]);

                // Execute job
                $payload = json_decode($job->payload, true) ?: [];
                if (class_exists($job->job_class)) {
                    $instance = new $job->job_class();
                    if (method_exists($instance, 'handle')) {
                        $instance->handle($payload);
                    }
                }

                $this->db->update('site_queue_jobs', [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                ], 'id = :id', [':id' => $job->id]);
                $processed++;
            } catch (\Exception $e) {
                Logger::error('Queue job failed', ['job_id' => $job->id, 'error' => $e->getMessage()]);
                $newStatus = $job->attempts >= $job->max_attempts ? 'failed' : 'pending';
                $this->db->update('site_queue_jobs', [
                    'status' => $newStatus,
                    'error' => substr($e->getMessage(), 0, 500),
                ], 'id = :id', [':id' => $job->id]);
            }
        }
        return $processed;
    }

    public function retry(int $jobId): bool
    {
        return $this->db->update('site_queue_jobs', [
            'status' => 'pending',
            'attempts' => 0,
            'error' => null,
            'available_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', [':id' => $jobId]);
    }

    public function purge(?string $queue = null): int
    {
        $where = "status IN ('completed','failed')";
        $params = [];
        if ($queue) { $where .= " AND queue_name = :q"; $params[':q'] = $queue; }
        $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_queue_jobs WHERE {$where}", $params);
        $count = $result ? (int)$result->cnt : 0;
        $this->db->query("DELETE FROM site_queue_jobs WHERE {$where}", $params);
        return $count;
    }

    public function size(?string $queue = null): int
    {
        $where = "status = 'pending'";
        $params = [];
        if ($queue) { $where .= " AND queue_name = :q"; $params[':q'] = $queue; }
        $result = $this->db->fetch("SELECT COUNT(*) as cnt FROM site_queue_jobs WHERE {$where}", $params);
        return $result ? (int)$result->cnt : 0;
    }
}