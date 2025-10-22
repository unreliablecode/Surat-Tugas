<?php
$page_title = 'All Request History';
include_once '../partials/header.php';
if (!isAdmin()) { header("Location: " . BASE_URL); exit; }
include_once '../partials/sidebar.php';

// Fetch all requests
$stmt = $pdo->prepare("
    SELECT r.*, u.name as requester_name 
    FROM requests r
    JOIN users u ON r.requester_id = u.id 
    ORDER BY r.created_at DESC
");
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-3xl font-semibold text-gray-800">All Request History</h2>

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($request['request_number']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($request['requester_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y', strtotime($request['letter_date'])) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                             <?php
                                $status_color = 'bg-yellow-100 text-yellow-800'; // Pending
                                if ($request['status'] === 'approved') $status_color = 'bg-green-100 text-green-800';
                                if ($request['status'] === 'declined') $status_color = 'bg-red-100 text-red-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_color ?>">
                                <?= ucfirst($request['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-3">
                            <!-- NEW: Preview Button -->
                            <button type="button" class="preview-btn text-blue-600 hover:text-blue-900" data-id="<?= $request['id'] ?>">Preview</button>
                            
                            <?php if ($request['status'] === 'approved'): ?>
                                <a href="../download.php?id=<?= $request['id'] ?>&format=docx" class="text-blue-600 hover:text-blue-900" title="Download DOCX"><i class="fa-solid fa-file-word"></i></a>
                                <a href="../download.php?id=<?= $request['id'] ?>&format=pdf" class="text-red-600 hover:text-red-900" title="Download PDF"><i class="fa-solid fa-file-pdf"></i></a>
                            <?php elseif ($request['status'] === 'pending'): ?>
                                <a href="approvals.php" class="text-yellow-600 hover:text-yellow-900">Review</a>
                            <?php else: ?>
                                <span class="text-gray-400">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once '../partials/footer.php'; ?>
