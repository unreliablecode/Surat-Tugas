<?php 
$page_title = 'User Dashboard';
include_once '../partials/header.php'; 
include_once '../partials/sidebar.php'; 
?>

<h2 class="text-3xl font-semibold text-gray-800">Dashboard</h2>
<p class="mt-2 text-gray-600">Welcome back, <?= htmlspecialchars($currentUser['name']); ?>!</p>

<div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="bg-blue-500 text-white rounded-full p-3">
                <i class="fa-solid fa-file-circle-plus fa-2x"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">New Assignment Letter</h3>
                <p class="text-gray-500 text-sm">Create a new request for technicians.</p>
            </div>
        </div>
        <a href="request.php" class="mt-4 inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition duration-300">
            Create Request
        </a>
    </div>

    <?php
        $stmt_stats = $pdo->prepare("SELECT 
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved 
            FROM requests WHERE requester_id = ?");
        $stmt_stats->execute([$_SESSION['user_id']]);
        $stats = $stmt_stats->fetch();
    ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">My Request Stats</h3>
        <div class="flex items-center mt-4">
            <div class="w-1/2">
                <p class="text-3xl font-bold text-yellow-500"><?= $stats['pending'] ?? 0 ?></p>
                <p class="text-gray-500">Pending</p>
            </div>
            <div class="w-1/2">
                <p class="text-3xl font-bold text-green-500"><?= $stats['approved'] ?? 0 ?></p>
                <p class="text-gray-500">Approved</p>
            </div>
        </div>
    </div>
</div>


<?php include_once '../partials/footer.php'; ?>
