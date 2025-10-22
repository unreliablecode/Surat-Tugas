<div class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="px-6 py-4 border-b border-gray-700">
        <h1 class="text-xl font-bold">Dashboard STO</h1>
        <!-- UPDATED: Added Profile Picture -->
        <div class="flex items-center mt-3">
            <img class="h-10 w-10 rounded-full object-cover" 
                 src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($currentUser['profile_path'] ?? 'default.png'); ?>" 
                 alt="Profile Photo">
            <p class="ml-3 text-sm text-gray-400">Welcome,<br><?= htmlspecialchars($currentUser['username']); ?></p>
        </div>
    </div>
    <nav class="flex-1 px-4 py-4 space-y-2">
        <?php if (isAdmin()): ?>
            <h3 class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin Menu</h3>
            <a href="<?= BASE_URL ?>/admin/index.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-tachograph-digital fa-fw mr-3"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/admin/approvals.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-check-to-slot fa-fw mr-3"></i> Approvals
            </a>
             <a href="<?= BASE_URL ?>/admin/history.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-clock-rotate-left fa-fw mr-3"></i> All Requests
            </a>
            <a href="<?= BASE_URL ?>/admin/users.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-users-gear fa-fw mr-3"></i> User Management
            </a>
            <a href="<?= BASE_URL ?>/admin/settings.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-cog fa-fw mr-3"></i> Site Settings
            </a>
        <?php else: ?>
            <h3 class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">User Menu</h3>
            <a href="<?= BASE_URL ?>/user/index.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-house fa-fw mr-3"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/user/request.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-file-circle-plus fa-fw mr-3"></i> New Request
            </a>
            <a href="<?= BASE_URL ?>/user/history.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-list-check fa-fw mr-3"></i> My History
            </a>
            <a href="<?= BASE_URL ?>/user/profile.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700">
                <i class="fa-solid fa-user-pen fa-fw mr-3"></i> My Profile
            </a>
        <?php endif; ?>
    </nav>
    <div class="px-4 py-4 border-t border-gray-700">
         <a href="<?= BASE_URL ?>/logout.php" class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700 text-red-400">
            <i class="fa-solid fa-right-from-bracket fa-fw mr-3"></i> Logout
        </a>
    </div>
</div>

<main class="flex-1 flex flex-col overflow-hidden">
    <div class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
        <div class="container mx-auto px-6 py-8">
