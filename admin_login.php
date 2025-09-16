<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center">Admin Login</h1>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="block mb-2">Username</label>
                    <input type="text" name="username" required class="w-full p-2 border rounded">
                </div>
                <div class="mb-6">
                    <label class="block mb-2">Password</label>
                    <input type="password" name="password" required class="w-full p-2 border rounded">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white p-2 rounded hover:bg-indigo-700">
                    Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>