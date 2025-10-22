<?php
$page_title = 'My Profile';
include_once '../partials/header.php';
include_once '../partials/sidebar.php';
?>

<h2 class="text-3xl font-semibold text-gray-800">My Profile & Settings</h2>

<div class="mt-8 bg-white p-8 rounded-lg shadow-md">
    <form action="../actions/update_profile.php" method="POST" enctype="multipart/form-data">
        
        <div class="flex items-center space-x-6 mb-8">
            <img class="h-24 w-24 rounded-full object-cover" src="../uploads/<?= htmlspecialchars($currentUser['profile_path'] ?? 'default.png'); ?>" alt="Current profile photo">
            <div>
                <label for="profile_picture" class="block text-sm font-medium text-gray-700">Change profile photo</label>
                <input type="file" name="profile_picture" id="profile_picture" class="mt-1 block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($currentUser['name']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
             <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($currentUser['username']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 sm:text-sm" readonly>
            </div>
             <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($currentUser['email']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                <input type="text" name="nik" id="nik" value="<?= htmlspecialchars($currentUser['nik']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                <input type="text" name="position" id="position" value="<?= htmlspecialchars($currentUser['position']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="psa" class="block text-sm font-medium text-gray-700">PSA</label>
                <input type="text" name="psa" id="psa" value="<?= htmlspecialchars($currentUser['psa']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <div class="mt-10 pt-6 border-t">
             <h3 class="text-lg font-semibold text-gray-800">Notification Settings</h3>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label for="discord_webhook" class="block text-sm font-medium text-gray-700">Discord Webhook URL</label>
                    <input type="url" name="discord_webhook" id="discord_webhook" value="<?= htmlspecialchars($currentUser['discord_webhook']); ?>" placeholder="https://discord.com/api/webhooks/..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                </div>
                <div>
                    <label for="telegram_chat_id" class="block text-sm font-medium text-gray-700">Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id" id="telegram_chat_id" value="<?= htmlspecialchars($currentUser['telegram_chat_id']); ?>" placeholder="Your numeric Telegram Chat ID" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                </div>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t">
            <h3 class="text-lg font-semibold text-gray-800">Change Password</h3>
            <p class="text-sm text-gray-500">Leave these fields blank if you don't want to change your password.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                 <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                <i class="fa-solid fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<?php include_once '../partials/footer.php'; ?>
