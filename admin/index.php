<?php
$page_title = 'Admin Dashboard';
include_once '../partials/header.php';

if (!isAdmin()) {
    header("Location: ". BASE_URL . "/user/index.php");
    exit;
}

include_once '../partials/sidebar.php';

// Stats (unchanged)
$pending_count = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
$approved_count = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'approved'")->fetchColumn();
$user_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// UPDATED: Fetch recent pending requests (with requester PFP)
$stmt_pending = $pdo->prepare("
    SELECT r.*, u.name as requester_name, u.profile_path as requester_pfp
    FROM requests r
    JOIN users u ON r.requester_id = u.id
    WHERE r.status = 'pending' 
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt_pending->execute();
$recent_pending = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);

// UPDATED: Fetch recent approved requests (with requester PFP)
$stmt_approved = $pdo->prepare("
    SELECT r.*, u.name as requester_name, u.profile_path as requester_pfp
    FROM requests r
    JOIN users u ON r.requester_id = u.id
    WHERE r.status = 'approved' 
    ORDER BY r.action_at DESC
    LIMIT 5
");
$stmt_approved->execute();
$recent_approved = $stmt_approved->fetchAll(PDO::FETCH_ASSOC);

// EFFICIENTLY Fetch all technicians for ALL requests on this page
$technicians_map = [];
$request_ids = array_merge(
    array_column($recent_pending, 'id'), 
    array_column($recent_approved, 'id')
);

if (!empty($request_ids)) {
    $id_placeholders = implode(',', array_fill(0, count($request_ids), '?'));
    
    $sql = "SELECT rt.*, u.profile_path 
            FROM request_technicians rt
            LEFT JOIN users u ON rt.nik = u.nik
            WHERE rt.request_id IN ($id_placeholders)";
    
    $tech_stmt = $pdo->prepare($sql);
    $tech_stmt->execute($request_ids);
    
    while ($tech = $tech_stmt->fetch(PDO::FETCH_ASSOC)) {
        $technicians_map[$tech['request_id']][] = $tech;
    }
}
?>

<h2 class="text-3xl font-semibold text-gray-800">Admin Dashboard</h2>
<p class="mt-2 text-gray-600">Overview of the system.</p>

<!-- Stats Cards (Unchanged) -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- ... (All 3 stats cards are unchanged) ... -->
</div>

<!-- Recent Pending Requests -->
<div class="mt-8 bg-white rounded-lg shadow-md"> <!-- Removed p-6 for full-width table -->
    <div class="flex justify-between items-center mb-4 p-6">
        <h3 class="text-xl font-semibold text-gray-700">Recent Pending Requests</h3>
        <a href="approvals.php" class="text-sm text-blue-600 hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Technicians</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($recent_pending)): ?>
                    <tr><td colspan="4" class="p-4 text-center text-gray-500">No pending requests.</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_pending as $request): ?>
                        <tr>
                            <!-- UPDATED: Requester Info -->
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded-full object-cover" src="../uploads/<?= htmlspecialchars($request['requester_pfp'] ?? 'default.png'); ?>" alt="">
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($request['requester_name']) ?></p>
                                        <p class="text-sm text-gray-500"><?= date('d M Y', strtotime($request['created_at'])) ?></p>
                                    </div>
                                </div>
                            </td>
                            <!-- Request Details -->
                            <td class="px-6 py-4 text-sm text-gray-600 align-top max-w-xs">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($request['request_number']) ?></p>
                                <p class="truncate"><?= htmlspecialchars($request['purpose_text']) ?></p>
                            </td>
                            <!-- UPDATED: Technician List -->
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <?php if (isset($technicians_map[$request['id']])): ?>
                                        <?php foreach($technicians_map[$request['id']] as $tech): ?>
                                            <div class="flex items-center">
                                                <img class="h-6 w-6 rounded-full object-cover" src="../uploads/<?= $tech['profile_path'] ?? 'default.png'; ?>" alt="">
                                                <p class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($tech['name']); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 text-sm font-medium align-top">
                                <div class="flex space-x-3">
                                    <button type="button" class="preview-btn text-blue-600 hover:text-blue-900" data-id="<?= $request['id'] ?>">Preview</button>
                                    <form action="../actions/approve_request.php" method="POST" class="inline approve-form">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <input type="hidden" name="action" value="approved">
                                        <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                    </form>
                                    <form action="../actions/approve_request.php" method="POST" class="inline decline-form">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                        <input type="hidden" name="action" value="declined">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Decline</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Approvals -->
<div class="mt-8 bg-white rounded-lg shadow-md"> <!-- Removed p-6 -->
    <div class="flex justify-between items-center mb-4 p-6">
        <h3 class="text-xl font-semibold text-gray-700">Recent Approvals</h3>
        <a href="history.php" class="text-sm text-blue-600 hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                 <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Technicians</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                 <?php if (empty($recent_approved)): ?>
                    <tr><td colspan="4" class="p-4 text-center text-gray-500">No approved requests yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_approved as $request): ?>
                         <tr>
                            <!-- UPDATED: Requester Info -->
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded-full object-cover" src="../uploads/<?= htmlspecialchars($request['requester_pfp'] ?? 'default.png'); ?>" alt="">
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($request['requester_name']) ?></p>
                                        <p class="text-sm text-gray-500">Approved: <?= date('d M Y', strtotime($request['action_at'])) ?></p>
                                    </div>
                                </div>
                            </td>
                            <!-- Request Details -->
                            <td class="px-6 py-4 text-sm text-gray-600 align-top max-w-xs">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($request['request_number']) ?></p>
                                <p class="truncate"><?= htmlspecialchars($request['purpose_text']) ?></p>
                            </td>
                            <!-- UPDATED: Technician List -->
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <?php if (isset($technicians_map[$request['id']])): ?>
                                        <?php foreach($technicians_map[$request['id']] as $tech): ?>
                                            <div class="flex items-center">
                                                <img class="h-6 w-6 rounded-full object-cover" src="../uploads/<?= $tech['profile_path'] ?? 'default.png'; ?>" alt="">
                                                <p class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($tech['name']); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 text-sm font-medium align-top">
                                <div class="flex space-x-3">
                                    <button type="button" class="preview-btn text-blue-600 hover:text-blue-900" data-id="<?= $request['id'] ?>">Preview</button>
                                    <a href="../download.php?id=<?= $request['id'] ?>&format=pdf" class="ml-2 text-red-600 hover:text-red-900" title="Download PDF"><i class="fa-solid fa-file-pdf"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- (JavaScript for SweetAlert is unchanged) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Approve Buttons ---
    const approveForms = document.querySelectorAll('.approve-form');
    approveForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); 
            Swal.fire({
                title: 'Approve Request?',
                text: "Are you sure you want to approve this request?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // --- Decline Buttons ---
    const declineForms = document.querySelectorAll('.decline-form');
    declineForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); 
            Swal.fire({
                title: 'Decline Request?',
                text: "Are you sure you want to decline this request? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, decline it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>

<?php include_once '../partials/footer.php'; ?>