<?php
return function (\Shared\Core\Database $db): array {
    $results = [];
    
    // Add SEO columns to site_blog_posts
    $columns = [
        'focus_keyword' => "VARCHAR(255) DEFAULT NULL",
        'featured_image' => "VARCHAR(500) DEFAULT NULL",
        'image_alt' => "VARCHAR(255) DEFAULT NULL",
    ];
    
    foreach ($columns as $col => $type) {
        try {
            $db->execute("ALTER TABLE site_blog_posts ADD COLUMN `{$col}` {$type}");
            $results[] = ['column' => $col, 'status' => 'added'];
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate column')) {
                $results[] = ['column' => $col, 'status' => 'exists'];
            } else {
                $results[] = ['column' => $col, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }
    }
    
    return $results;
};