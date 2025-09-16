<?php if (!isset($_SESSION['admin_logged_in'])) return; ?>
<header class="bg-white shadow-sm">
    <div class="flex items-center justify-between p-4">
        <button id="sidebarToggle" class="md:hidden text-gray-600">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <div class="flex items-center">
            <div class="mr-4">
                <span class="text-gray-600">Welcome, Admin</span>
            </div>
            <a href="logout.php" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </div>
</header>