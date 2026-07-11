<?php
namespace Shared\Interfaces;

interface SearchIndexInterface
{
    public function index(string $entityType, int $entityId, array $data): void;
    public function remove(string $entityType, int $entityId): void;
    public function search(string $query, array $filters = [], string $sort = 'relevance', int $page = 1, int $perPage = 20): object;
    public function autocomplete(string $term, int $limit = 8): array;
    public function reindex(?string $entityType = null): void;
    public function getStats(): array;
}