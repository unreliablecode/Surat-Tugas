<?php
$page_title = 'My Request History';
include_once '../partials/header.php';
include_once '../partials/sidebar.php';

// Fetch requests for the current user
$stmt = $pdo->prepare("SELECT * FROM requests WHERE requester_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-3xl font-semibold text-gray-800">My Request History</h2>
<p class="mt-2 text-gray-600">Here is a list of all the assignment letters you have requested.</p>

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request #</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">You have not made any requests yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($request['request_number']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y', strtotime($request['letter_date'])); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($request['purpose_text']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php
                                    $status_color = 'bg-yellow-100 text-yellow-800'; // Pending
                                    if ($request['status'] === 'approved') $status_color = 'bg-green-100 text-green-800';
                                    if ($request['status'] === 'declined') $status_color = 'bg-red-100 text-red-800';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_color ?>">
                                    <?= ucfirst($request['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($request['status'] === 'approved'): ?>
                                    <a href="../download.php?id=<?= $request['id']; ?>&format=docx" class="text-blue-600 hover:text-blue-900" title="Download as DOCX">
                                        <i class="fa-solid fa-file-word fa-lg"></i>
                                    </a>
                                    <a href="../download.php?id=<?= $request['id']; ?>&format=pdf" class="ml-4 text-red-600 hover:text-red-900" title="Download as PDF">
                                        <i class="fa-solid fa-file-pdf fa-lg"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once '../partials/footer.php'; ?>
