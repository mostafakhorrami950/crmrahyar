<?php
namespace Shared\Migrations;

class Migration010
{
    public static function up($db): void
    {
        // Add SEO columns to blog posts
        $columns = [
            'focus_keyword' => "VARCHAR(255) DEFAULT NULL",
            'featured_image' => "VARCHAR(500) DEFAULT NULL",
            'image_alt' => "VARCHAR(255) DEFAULT NULL",
        ];
        
        foreach ($columns as $col => $type) {
            try {
                $db->execute("ALTER TABLE site_blog_posts ADD COLUMN `{$col}` {$type}");
            } catch (\Exception $e) {
                if (!str_contains($e->getMessage(), 'Duplicate column')) {
                    throw $e;
                }
            }
        }

        // Create SEO keywords table
        try {
            $db->execute("CREATE TABLE IF NOT EXISTS site_seo_keywords (
                id INT AUTO_INCREMENT PRIMARY KEY,
                keyword VARCHAR(255) NOT NULL,
                keyword_slug VARCHAR(255) NOT NULL,
                target_url VARCHAR(500) DEFAULT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {
            // Table may already exist
        }
    }
}