<?php
// create_admin.php
require 'config.php';

$username = 'Samuel';
$password = 'admin123'; // Change this

// Generate hash
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    echo "Admin user created successfully!";
} catch (PDOException $e) {
    die("Error creating admin: " . $e->getMessage());
}