<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

if (isset($_GET['id'])) {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // 1. Delete related plays first
        $pdo->prepare("DELETE FROM plays WHERE podcast_id = ?")
           ->execute([$_GET['id']]);

        // 2. Get podcast details
        $stmt = $pdo->prepare("SELECT * FROM podcasts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $podcast = $stmt->fetch();

        // 3. Delete files if they exist
        if ($podcast) {
            $audio_path = $upload_dir.'audio/'.$podcast['audio_file'];
            $image_path = $upload_dir.'images/'.$podcast['cover_image'];
            
            if (file_exists($audio_path)) unlink($audio_path);
            if (file_exists($image_path)) unlink($image_path);
        }

        // 4. Delete the podcast
        $pdo->prepare("DELETE FROM podcasts WHERE id = ?")
           ->execute([$_GET['id']]);

        // Commit transaction
        $pdo->commit();

    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        die("Error deleting podcast: " . $e->getMessage());
    }
}

header('Location: admin_dashboard.php');
exit;
?>