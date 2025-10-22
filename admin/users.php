<?php
$page_title = 'User Management';
include_once '../partials/header.php';
if (!isAdmin()) { header("Location: " . BASE_URL); exit; }
include_once '../partials/sidebar.php';

// --- EDIT LOGIC: Check if we are editing a user ---
$editing_user = null;
$form_action = 'create';
$form_title = 'Create New User';
$button_text = 'Create User';

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editing_user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editing_user) {
        $form_action = 'update';
        $form_title = 'Edit User: ' . htmlspecialchars($editing_user['username']);
        $button_text = 'Save Changes';
    }
}

// --- DELETE LOGIC ---
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    // Prevent admin from deleting their own account
    if ($_GET['delete'] == $_SESSION['user_id']) {
        header("location: users.php?error=" . urlencode("You cannot delete your own account."));
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$_GET['delete']])) {
         header("location: users.php?success=" . urlencode("User deleted successfully."));
         exit;
    } else {
         header("location: users.php?error=" . urlencode("Failed to delete user."));
         exit;
    }
}

// --- Fetch all users for the table ---
$all_users = $pdo->query("SELECT * FROM users ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-3xl font-semibold text-gray-800">User Management</h2>

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-semibold text-gray-700 mb-4"><?= $form_title ?></h3>
    <form action="../actions/user_management.php" method="POST">
        <input type="hidden" name="action" value="<?= $form_action ?>">
        <?php if ($editing_user): ?>
            <input type="hidden" name="user_id" value="<?= $editing_user['id'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <input type="text" name="name" placeholder="Full Name" class="p-2 border rounded" value="<?= htmlspecialchars($editing_user['name'] ?? '') ?>" required>
            <input type="text" name="username" placeholder="Username" class="p-2 border rounded" value="<?= htmlspecialchars($editing_user['username'] ?? '') ?>" required>
            <input type="email" name="email" placeholder="Email" class="p-2 border rounded" value="<?= htmlspecialchars($editing_user['email'] ?? '') ?>" required>
            <input type="password" name="password" placeholder="Password (leave blank to keep same)" class="p-2 border rounded">
            <select name="role" class="p-2 border rounded" required>
                <option value="user" <?= (($editing_user['role'] ?? 'user') == 'user') ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= (($editing_user['role'] ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
            <input type="text" name="nik" placeholder="NIK" class="p-2 border rounded" value="<?= htmlspecialchars($editing_user['nik'] ?? '') ?>">
            <input type="text" name="position" placeholder="Position" class="p-2 border rounded" value="<?= htmlspecialchars($editing_user['position'] ?? '') ?>">
            <input type="text" name="psa" placeholder="PSA" class="p-2 border rounded" value="<?= htmlspecialchars($editing_user['psa'] ?? '') ?>">
        </div>
        <div class="mt-4 flex justify-end space-x-3">
             <?php if ($editing_user): ?>
                <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Cancel Edit</a>
            <?php endif; ?>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded"><?= $button_text ?></button>
        </div>
    </form>
</div>

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-semibold text-gray-700 mb-4">All Users</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($all_users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= ucfirst($user['role']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($user['nik']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="users.php?edit=<?= $user['id'] ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): // Don't show delete for self ?>
                                <a href="users.php?delete=<?= $user['id'] ?>" class="ml-4 text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once '../partials/footer.php'; ?>
