<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header("location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $nik = trim($_POST['nik']);
    $position = trim($_POST['position']);
    $psa = trim($_POST['psa']);
    $discord_webhook = trim($_POST['discord_webhook']);
    $telegram_chat_id = trim($_POST['telegram_chat_id']);

    $sql_parts = [];
    $params = [];

    // Basic user info
    $sql_parts[] = "name = ?"; $params[] = $name;
    $sql_parts[] = "email = ?"; $params[] = $email;
    $sql_parts[] = "nik = ?"; $params[] = $nik;
    $sql_parts[] = "position = ?"; $params[] = $position;
    $sql_parts[] = "psa = ?"; $params[] = $psa;
    $sql_parts[] = "discord_webhook = ?"; $params[] = $discord_webhook;
    $sql_parts[] = "telegram_chat_id = ?"; $params[] = $telegram_chat_id;

    // --- HANDLE PROFILE PICTURE UPLOAD ---
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "../uploads/";
        $file_extension = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
        $new_filename = $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate file
        if (in_array(strtolower($file_extension), $allowed_types) && $_FILES["profile_picture"]["size"] < 5000000) { // 5MB limit
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $sql_parts[] = "profile_path = ?";
                $params[] = $new_filename;
            } else {
                 header("location: ../user/profile.php?error=" . urlencode("Failed to upload profile picture."));
                 exit;
            }
        } else {
            header("location: ../user/profile.php?error=" . urlencode("Invalid file type or size too large. (Max 5MB, JPG, PNG, GIF)"));
            exit;
        }
    }

    // --- HANDLE PASSWORD CHANGE ---
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            header("location: ../user/profile.php?error=" . urlencode("New passwords do not match."));
            exit;
        }
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $sql_parts[] = "password = ?";
        $params[] = $hashed_password;
    }

    // --- BUILD AND EXECUTE THE FINAL QUERY ---
    if (!empty($sql_parts)) {
        $sql = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE id = ?";
        $params[] = $user_id;

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            header("location: ../user/profile.php?success=" . urlencode("Profile updated successfully."));
        } catch (PDOException $e) {
            // Check for duplicate email error
            if ($e->getCode() == 23000) { 
                 header("location: ../user/profile.php?error=" . urlencode("That email address is already in use."));
            } else {
                 header("location: ../user/profile.php?error=" . urlencode("Database error: " . $e->getMessage()));
            }
        }
    } else {
         header("location: ../user/profile.php?success=" . urlencode("No changes were made."));
    }

}
?>
