<?php
require_once '../config.php';
require_once '../lib/notifications.php';

if (!isAdmin()) {
    header("Location: " . BASE_URL);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $action = $_POST['action']; // 'approved' or 'declined'

    if (empty($request_id) || !in_array($action, ['approved', 'declined'])) {
        header("location: ../admin/approvals.php?error=Invalid action.");
        exit;
    }

    try {
        // Fetch request details before updating
        $stmt_fetch = $pdo->prepare("SELECT requester_id, request_number FROM requests WHERE id = ?");
        $stmt_fetch->execute([$request_id]);
        $request = $stmt_fetch->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
             header("location: ../admin/approvals.php?error=Request not found.");
             exit;
        }

        // Update the request
        $sql = "UPDATE requests SET status = ?, approver_id = ?, action_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$action, $_SESSION['user_id'], $request_id]);

        // Send notification to the original requester
        $requester_id = $request['requester_id'];
        $request_number = $request['request_number'];
        $message = "Your assignment request #{$request_number} has been {$action} by an administrator.";
        
        sendNotificationToUser($pdo, $requester_id, $message);

        header("location: ../admin/approvals.php?success=Request has been {$action}.");

    } catch (PDOException $e) {
        header("location: ../admin/approvals.php?error=Database error: " . $e->getMessage());
    }
} else {
    header("location: ../admin/approvals.php");
}
?>
