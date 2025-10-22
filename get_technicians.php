<?php
require_once 'config.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT name, nik, position, psa FROM users WHERE role = 'user' AND (name LIKE :query OR nik LIKE :query)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => "%$query%"]);
$technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($technicians);
?>
