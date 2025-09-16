<?php
require 'config.php';

if (isset($_GET['id'])) {
    // Update episode play count
    $pdo->prepare("UPDATE episodes SET play_count = play_count + 1 WHERE id = ?")
       ->execute([$_GET['id']]);
    
    // Record individual play
    $pdo->prepare("INSERT INTO plays (episode_id) VALUES (?)")
       ->execute([$_GET['id']]);
}
?>