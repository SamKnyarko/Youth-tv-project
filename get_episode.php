<?php
// get_episode.php
require 'config.php';

if (isset($_GET['id'])) {
    $episodeId = $_GET['id'];
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, ps.title AS series_title, ps.cover_image AS series_cover
            FROM episodes e
            JOIN podcast_series ps ON e.series_id = ps.id
            WHERE e.id = ?
        ");
        $stmt->execute([$episodeId]);
        $episode = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($episode) {
            header('Content-Type: application/json');
            echo json_encode($episode);
            exit;
        }
    } catch (PDOException $e) {
        // Error handling
    }
}

http_response_code(404);
echo json_encode(['error' => 'Episode not found']);