<?php
require_once '../config.php';

// Check if registration is enabled by admin
$stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'enable_registration'");
$registration_enabled = $stmt->fetchColumn();

if ($registration_enabled !== 'true') {
    header("location: ../register.php?error=" . urlencode("Registration is currently disabled by the administrator."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validations
    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        header("location: ../register.php?error=" . urlencode("Please fill out all fields."));
        exit;
    }

    if ($password !== $confirm_password) {
        header("location: ../register.php?error=" . urlencode("Passwords do not match."));
        exit;
    }
    
    // Check if username or email already exists
    $sql = "SELECT id FROM users WHERE username = :username OR email = :email";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            header("location: ../register.php?error=" . urlencode("Username or email already taken."));
            exit;
        }
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    $sql = "INSERT INTO users (name, username, email, password) VALUES (:name, :username, :email, :password)";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);

        if ($stmt->execute()) {
            header("location: ../login.php?success=" . urlencode("Registration successful! Please log in."));
        } else {
            header("location: ../register.php?error=" . urlencode("Something went wrong. Please try again."));
        }
    }
    unset($stmt);
    unset($pdo);
}
?>
