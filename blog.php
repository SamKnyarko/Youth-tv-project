<?php
require 'config.php';

// Get page number from query string, default to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6; // Number of posts per page
$offset = ($page > 1) ? ($page - 1) * $perPage : 0;

// Get category filter if exists
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;
$tagFilter = isset($_GET['tag']) ? $_GET['tag'] : null;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : null;

// Build the query
$query = "SELECT p.*, u.username as author_name, c.name as category_name 
          FROM blog_posts p
          JOIN users u ON p.author_id = u.id
          LEFT JOIN blog_categories c ON p.category_id = c.id
          WHERE p.status = 'published' AND p.published_at <= NOW()";

$params = [];

if ($categoryFilter) {
    $query .= " AND c.slug = ?";
    $params[] = $categoryFilter;
}

if ($tagFilter) {
    $query .= " AND p.id IN (
        SELECT post_id FROM blog_post_tags pt
        JOIN blog_tags t ON pt.tag_id = t.id
        WHERE t.slug = ?
    )";
    $params[] = $tagFilter;
}

if ($searchQuery) {
    $query .= " AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY p.published_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

// Get posts
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM blog_posts p
               JOIN users u ON p.author_id = u.id
               LEFT JOIN blog_categories c ON p.category_id = c.id
               WHERE p.status = 'published' AND p.published_at <= NOW()";

if ($categoryFilter) {
    $countQuery .= " AND c.slug = ?";
}

if ($tagFilter) {
    $countQuery .= " AND p.id IN (
        SELECT post_id FROM blog_post_tags pt
        JOIN blog_tags t ON pt.tag_id = t.id
        WHERE t.slug = ?
    )";
}

if ($searchQuery) {
    $countQuery .= " AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalPosts = $countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $perPage);

// Get categories for sidebar
$categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name")->fetchAll();

// Get popular tags
$tags = $pdo->query("SELECT t.*, COUNT(pt.post_id) as post_count 
                     FROM blog_tags t
                     JOIN blog_post_tags pt ON t.id = pt.tag_id
                     GROUP BY t.id
                     ORDER BY post_count DESC
                     LIMIT 15")->fetchAll();

// Get recent posts for sidebar
$recentPosts = $pdo->query("SELECT id, title, slug, published_at 
                            FROM blog_posts 
                            WHERE status = 'published' AND published_at <= NOW()
                            ORDER BY published_at DESC 
                            LIMIT 5")->fetchAll();
?>