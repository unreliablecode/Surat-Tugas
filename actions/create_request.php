<?php
require_once '../config.php';
// Include notification library (we will create this later)
// require_once '../lib/notifications.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("location: ../login.php?error=Please log in to make a request.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- FORM DATA ---
    $request_number = trim($_POST['request_number']);
    $letter_date = trim($_POST['letter_date']);
    $purpose_text = trim($_POST['purpose_text']);
    $requester_id = $_SESSION['user_id'];
    
    // Technician Data (arrays)
    $teknisi_nama = $_POST['teknisi_nama'];
    $teknisi_nik = $_POST['teknisi_nik'];
    $teknisi_posisi = $_POST['teknisi_posisi'];
    $teknisi_psa = $_POST['teknisi_psa'];

    // --- VALIDATION ---
    if (empty($request_number) || empty($letter_date) || empty($purpose_text) || count($teknisi_nama) == 0) {
        header("location: ../user/request.php?error=" . urlencode("All fields are required."));
        exit;
    }
    
    // Check for duplicate request number
    $stmt = $pdo->prepare("SELECT id FROM requests WHERE request_number = ?");
    $stmt->execute([$request_number]);
    if ($stmt->fetch()) {
        header("location: ../user/request.php?error=" . urlencode("Duplicate request number detected. Please refresh and try again."));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert into 'requests' table
        $sql_request = "INSERT INTO requests (request_number, requester_id, purpose_text, letter_date, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt_request = $pdo->prepare($sql_request);
        $stmt_request->execute([$request_number, $requester_id, $purpose_text, $letter_date]);
        
        // Get the ID of the request we just inserted
        $last_request_id = $pdo->lastInsertId();

        // 2. Insert each technician into 'request_technicians' table
        $sql_tech = "INSERT INTO request_technicians (request_id, name, nik, position, psa) VALUES (?, ?, ?, ?, ?)";
        $stmt_tech = $pdo->prepare($sql_tech);

        for ($i = 0; $i < count($teknisi_nama); $i++) {
            if (!empty($teknisi_nama[$i])) {
                 $stmt_tech->execute([
                    $last_request_id,
                    trim($teknisi_nama[$i]),
                    trim($teknisi_nik[$i]),
                    trim($teknisi_posisi[$i]),
                    trim($teknisi_psa[$i])
                ]);
            }
        }

        $pdo->commit();
        
        // --- SEND NOTIFICATION TO ADMINS (will uncomment when notifications.php is ready) ---
        // $message = "New assignment request #{$request_number} has been submitted and is awaiting approval.";
        // sendNotificationToAdmins($pdo, $message);
        // --- SEND NOTIFICATION TO ADMINS ---
        require_once '../lib/notifications.php';
        $message = "New assignment request #{$request_number} has been submitted by a user and is awaiting your approval.";
        sendNotificationToAdmins($pdo, $message);
        header("location: ../user/history.php?success=" . urlencode("Request successfully submitted and is now pending approval."));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("location: ../user/request.php?error=" . urlencode("Database error: " . $e->getMessage()));
        exit;
    }
}
?>
