<?php
namespace Shared\Migrations;

class Migration010
{
    public static function up($db): void
    {
        $columns = [
            'focus_keyword' => "VARCHAR(255) DEFAULT NULL",
            'featured_image' => "VARCHAR(500) DEFAULT NULL",
            'image_alt' => "VARCHAR(255) DEFAULT NULL",
        ];
        
        foreach ($columns as $col => $type) {
            try {
                $db->execute("ALTER TABLE site_blog_posts ADD COLUMN `{$col}` {$type}");
            } catch (\Exception $e) {
                // Column already exists - ignore
                if (!str_contains($e->getMessage(), 'Duplicate column')) {
                    throw $e;
                }
            }
        }
    }
}