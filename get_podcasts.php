<?php
require 'config.php';

header('Content-Type: application/json');

try {
    // Get limit parameter from query string (default to 3)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;

    // Prepare and execute the query to get latest episodes with their series info
    $stmt = $pdo->prepare("
        SELECT 
            e.id,
            e.title,
            e.description,
            e.audio_file,
            e.duration,
            ps.title AS series_title,
            ps.cover_image AS series_cover
        FROM 
            episodes e
        JOIN 
            podcast_series ps ON e.series_id = ps.id
        ORDER BY 
            e.created_at DESC
        LIMIT :limit
    ");
    
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $podcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert duration from seconds to minutes:seconds format
    foreach ($podcasts as &$podcast) {
        $podcast['duration'] = (int)$podcast['duration']; // Ensure it's an integer
        // Add full paths to files if they're stored as relative paths
        $podcast['audio_file'] = $audio_web_path . $podcast['audio_file'];
        $podcast['series_cover'] = $image_web_path . $podcast['series_cover'];
    }
    
    echo json_encode($podcasts);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>