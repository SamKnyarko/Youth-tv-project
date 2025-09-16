<?php
require 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_series.php');
    exit;
}

$series_id = $_GET['id'];

// Delete the series
$stmt = $pdo->prepare("DELETE FROM podcast_series WHERE id = ?");
$stmt->execute([$series_id]);

// Since we have ON DELETE CASCADE, episodes will be automatically deleted

// Delete cover image file
$cover_stmt = $pdo->prepare("SELECT cover_image FROM podcast_series WHERE id = ?");
$cover_stmt->execute([$series_id]);
$cover_image = $cover_stmt->fetchColumn();

if ($cover_image && file_exists($upload_dir.'images/'.$cover_image)) {
    unlink($upload_dir.'images/'.$cover_image);
}

// Redirect with success message
header('Location: manage_series.php?deleted=1');
exit;
?>