<?php
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("location: ../login.php?error=Username and password are required.");
        exit;
    }

    $sql = "SELECT id, username, password, role FROM users WHERE username = :username";
    
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    if (password_verify($password, $row['password'])) {
                        // Password is correct, so start a new session
                        $_SESSION["user_id"] = $row['id'];
                        $_SESSION["username"] = $row['username'];
                        $_SESSION["role"] = $row['role'];
                        
                        // Redirect user based on role
                        if ($row['role'] === 'admin') {
                            header("location: ../admin/index.php");
                        } else {
                            header("location: ../user/index.php");
                        }
                    } else {
                        header("location: ../login.php?error=Invalid password.");
                    }
                }
            } else {
                header("location: ../login.php?error=No account found with that username.");
            }
        } else {
            header("location: ../login.php?error=Oops! Something went wrong. Please try again later.");
        }
        unset($stmt);
    }
    unset($pdo);
}
?>
