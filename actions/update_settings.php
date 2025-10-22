<?php
require_once '../config.php';
if (!isAdmin()) { header("Location: " . BASE_URL); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $settings_to_update = [
        'enable_registration' => $_POST['enable_registration'] ?? 'false',
        'enable_discord' => $_POST['enable_discord'] ?? 'false',
        'enable_telegram' => $_POST['enable_telegram'] ?? 'false',
        'telegram_bot_token' => trim($_POST['telegram_bot_token'] ?? '')
    ];

    $sql = "UPDATE site_settings SET setting_value = ? WHERE setting_key = ?";
    $stmt = $pdo->prepare($sql);

    try {
        foreach ($settings_to_update as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        header("location: ../admin/settings.php?success=Settings updated successfully.");
    } catch (PDOException $e) {
        header("location: ../admin/settings.php?error=Database error: " . $e->getMessage());
    }
}
?>
