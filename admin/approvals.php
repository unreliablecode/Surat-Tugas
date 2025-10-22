<?php
$page_title = 'Request Approvals';
include_once '../partials/header.php';
if (!isAdmin()) { header("Location: ". BASE_URL); exit; }
include_once '../partials/sidebar.php';

// Fetch pending requests with requester's name AND PFP
$stmt = $pdo->prepare("
    SELECT r.*, u.name as requester_name, u.profile_path as requester_pfp
    FROM requests r
    JOIN users u ON r.requester_id = u.id
    WHERE r.status = 'pending' 
    ORDER BY r.created_at ASC
");
$stmt->execute();
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// EFFICIENTLY Fetch all technicians for these pending requests
$technicians_map = [];
if (!empty($pending_requests)) {
    $request_ids = array_column($pending_requests, 'id');
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

<h2 class="text-3xl font-semibold text-gray-800">Pending Assignment Requests</h2>

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <?php if (empty($pending_requests)): ?>
        <p class="text-center text-gray-500 py-8">No pending requests to review. Great job!</p>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($pending_requests as $request): ?>
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <!-- UPDATED: Requester Info with PFP -->
                            <div class="flex items-center mb-2">
                                <img class="h-8 w-8 rounded-full object-cover" 
                                     src="../uploads/<?= htmlspecialchars($request['requester_pfp'] ?? 'default.png'); ?>" 
                                     alt="<?= htmlspecialchars($request['requester_name']); ?>">
                                <p class="ml-2 text-sm text-gray-500">Requester: <?= htmlspecialchars($request['requester_name']); ?></p>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($request['request_number']); ?></h3>
                            <p class="mt-1 text-sm text-gray-600"><strong>Purpose:</strong> <?= htmlspecialchars($request['purpose_text']); ?></p>
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-3">
                             <!-- Preview Button -->
                            <button type="button" class="preview-btn px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600" data-id="<?= $request['id'] ?>">
                                <i class="fa-solid fa-eye mr-2"></i>Preview
                            </button>
                            
                             <!-- FIXED: Approve/Decline Forms -->
                             <form action="../actions/approve_request.php" method="POST" class="approve-form" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?= $request['id']; ?>">
                                <input type="hidden" name="action" value="approved">
                                <button type="submit" class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-md hover:bg-green-600">
                                    Approve
                                </button>
                            </form>
                             <form action="../actions/approve_request.php" method="POST" class="decline-form" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?= $request['id']; ?>">
                                <input type="hidden" name="action" value="declined">
                                <button type="submit" class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-md hover:bg-red-600">
                                    Decline
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- UPDATED: Detailed Technician List with PFPs -->
                    <div class="mt-4 pt-4 border-t">
                        <h4 class="text-sm font-semibold text-gray-700">Assigned Technicians:</h4>
                        <div class="mt-2 space-y-2">
                            <?php if (isset($technicians_map[$request['id']])): ?>
                                <?php foreach($technicians_map[$request['id']] as $tech): ?>
                                    <?php 
                                        // Use technician's PFP if they have an account, otherwise default.png
                                        $pfp_path = '../uploads/' . ($tech['profile_path'] ?? 'default.png'); 
                                    ?>
                                    <div class="flex items-center p-2 bg-gray-50 rounded-md">
                                        <img class="h-8 w-8 rounded-full object-cover" src="<?= $pfp_path; ?>" alt="">
                                        <div class="ml-3 text-sm">
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($tech['name']); ?></p>
                                            <p class="text-gray-600">NIK: <?= htmlspecialchars($tech['nik']); ?> | Posisi: <?= htmlspecialchars($tech['position']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No technicians assigned to this request.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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

