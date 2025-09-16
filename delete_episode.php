<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_episodes.php');
    exit;
}

$episode_id = $_GET['id'];

// First get the audio file path
$stmt = $pdo->prepare("SELECT audio_file FROM episodes WHERE id = ?");
$stmt->execute([$episode_id]);
$audio_file = $stmt->fetchColumn();

// Delete the episode
$delete_stmt = $pdo->prepare("DELETE FROM episodes WHERE id = ?");
$delete_stmt->execute([$episode_id]);

// Delete the audio file
if ($audio_file && file_exists($upload_dir.'audio/'.$audio_file)) {
    unlink($upload_dir.'audio/'.$audio_file);
}

// Get the series ID to redirect back
$series_stmt = $pdo->prepare("SELECT series_id FROM episodes WHERE id = ?");
$series_stmt->execute([$episode_id]);
$series_id = $series_stmt->fetchColumn();

// Redirect with success message
header("Location: manage_episodes.php?series_id=$series_id&deleted=1");
exit;
?>