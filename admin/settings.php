<?php
$page_title = 'Site Settings';
include_once '../partials/header.php';
if (!isAdmin()) { header("Location: " . BASE_URL); exit; }
include_once '../partials/sidebar.php';

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<h2 class="text-3xl font-semibold text-gray-800">Site Settings</h2>

<div class="mt-8 bg-white p-8 rounded-lg shadow-md max-w-2xl">
    <form action="../actions/update_settings.php" method="POST">
        <div class="mb-6">
            <label class="block text-lg font-medium text-gray-700">User Registration</label>
            <div class="mt-2 space-x-4">
                <label><input type="radio" name="enable_registration" value="true" <?= ($settings['enable_registration'] ?? 'true') == 'true' ? 'checked' : '' ?>> Enabled</label>
                <label><input type="radio" name="enable_registration" value="false" <?= ($settings['enable_registration'] ?? 'true') == 'false' ? 'checked' : '' ?>> Disabled</label>
            </div>
        </div>

        <div class="mb-6 pt-4 border-t">
            <label class="block text-lg font-medium text-gray-700">Notification Channels</label>
             <div class="mt-2 space-x-4">
                <label><input type="radio" name="enable_discord" value="true" <?= ($settings['enable_discord'] ?? 'true') == 'true' ? 'checked' : '' ?>> Enabled</label>
                <label><input type="radio" name="enable_discord" value="false" <?= ($settings['enable_discord'] ?? 'true') == 'false' ? 'checked' : '' ?>> Disabled</label>
            </div>
        </div>
        <div class="mb-6">
             <div class="mt-2 space-x-4">
                <label><input type="radio" name="enable_telegram" value="true" <?= ($settings['enable_telegram'] ?? 'true') == 'true' ? 'checked' : '' ?>> Enabled</label>
                <label><input type="radio" name="enable_telegram" value="false" <?= ($settings['enable_telegram'] ?? 'true') == 'false' ? 'checked' : '' ?>> Disabled</label>
            </div>
        </div>

        <div class="mb-6 pt-4 border-t">
            <label for="telegram_bot_token" class="block text-lg font-medium text-gray-700">Telegram Bot Token</label>
            <input type="text" name="telegram_bot_token" id="telegram_bot_token" value="<?= htmlspecialchars($settings['telegram_bot_token'] ?? '') ?>" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Enter your Telegram bot token here">
        </div>

        <div class="flex justify-end mt-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">Save Settings</button>
        </div>
    </form>
</div>

<?php include_once '../partials/footer.php'; ?>
