<?php
require_once '../config.php';
if (!isAdmin()) { header("Location: " . BASE_URL); exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // --- CREATE USER ---
    if ($action === 'create') {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $nik = trim($_POST['nik']);
        $position = trim($_POST['position']);
        $psa = trim($_POST['psa']);

        if (empty($name) || empty($username) || empty($email) || empty($password) || empty($role)) {
            header("location: ../admin/users.php?error=" . urlencode("Required fields are missing for user creation."));
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (name, username, email, password, role, nik, position, psa) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $pdo->prepare($sql)->execute([$name, $username, $email, $hashed_password, $role, $nik, $position, $psa]);
            header("location: ../admin/users.php?success=" . urlencode("User created successfully."));
        } catch (PDOException $e) {
            header("location: ../admin/users.php?error=" . urlencode("Failed to create user. Username or email may already exist."));
        }
    }

    // --- UPDATE USER ---
    if ($action === 'update') {
        $user_id = $_POST['user_id'];
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $nik = trim($_POST['nik']);
        $position = trim($_POST['position']);
        $psa = trim($_POST['psa']);

        if (empty($user_id) || empty($name) || empty($username) || empty($email) || empty($role)) {
            header("location: ../admin/users.php?error=Required fields are missing.");
            exit;
        }

        $params = [$name, $username, $email, $role, $nik, $position, $psa];
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET name=?, username=?, email=?, role=?, nik=?, position=?, psa=?, password=? WHERE id=?";
            $params[] = $hashed_password;
        } else {
            $sql = "UPDATE users SET name=?, username=?, email=?, role=?, nik=?, position=?, psa=? WHERE id=?";
        }
        $params[] = $user_id;

        try {
            $pdo->prepare($sql)->execute($params);
            header("location: ../admin/users.php?success=User updated successfully.");
        } catch (PDOException $e) {
             header("location: ../admin/users.php?error=" . urlencode("Failed to update user. Username or email may already exist."));
        }
    }
}
?>
