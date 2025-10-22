<?php
// This file requires a config.php to be included before it.

function sendToDiscord($webhook_url, $message) {
    if (empty($webhook_url)) return false;

    $data = ['content' => $message];
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];
    $context  = stream_context_create($options);
    return file_get_contents($webhook_url, false, $context);
}

function sendToTelegram($bot_token, $chat_id, $message) {
    if (empty($bot_token) || empty($chat_id)) return false;

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $message, 'parse_mode' => 'HTML'];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}


// --- Main Notification Functions ---

// Notify all admins
function sendNotificationToAdmins($pdo, $message) {
    $stmt_settings = $pdo->query("SELECT * FROM site_settings");
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Check if notifications are enabled globally
    $discord_enabled = $settings['enable_discord'] === 'true';
    $telegram_enabled = $settings['enable_telegram'] === 'true';
    
    $stmt_admins = $pdo->query("SELECT discord_webhook, telegram_chat_id FROM users WHERE role = 'admin'");
    while ($admin = $stmt_admins->fetch(PDO::FETCH_ASSOC)) {
        if ($discord_enabled && !empty($admin['discord_webhook'])) {
            sendToDiscord($admin['discord_webhook'], $message);
        }
        // NOTE: Telegram Bot token needs to be stored in site settings for this to work
        if ($telegram_enabled && !empty($admin['telegram_chat_id']) && !empty($settings['telegram_bot_token'])) {
             sendToTelegram($settings['telegram_bot_token'], $admin['telegram_chat_id'], $message);
        }
    }
}

// Notify a specific user
function sendNotificationToUser($pdo, $user_id, $message) {
    $stmt_settings = $pdo->query("SELECT * FROM site_settings");
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

    $discord_enabled = $settings['enable_discord'] === 'true';
    $telegram_enabled = $settings['enable_telegram'] === 'true';

    $stmt_user = $pdo->prepare("SELECT discord_webhook, telegram_chat_id FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($discord_enabled && !empty($user['discord_webhook'])) {
            sendToDiscord($user['discord_webhook'], $message);
        }
         if ($telegram_enabled && !empty($user['telegram_chat_id']) && !empty($settings['telegram_bot_token'])) {
             sendToTelegram($settings['telegram_bot_token'], $user['telegram_chat_id'], $message);
        }
    }
}

?>
