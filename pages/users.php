<!DOCTYPE html>
<html lang="en">

<?php
    include '../includes/header.php';
    require_once '../config/config.php';
    global $pdo;

    // Check user session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verify if the user is an admin
    if (!isset($_SESSION['user']) || !hasPermission('admin')) {
        header('Location: /?page=home');
        exit;
    }

    // Handle POST actions for updating roles or blocking users
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = intval($_POST['user_id'] ?? 0);

        // Update user role
        if (isset($_POST['role'])) {
            $newRole = $_POST['role'];
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$newRole, $userId]);
        
                // Ajouter l'action dans l'audit_logs
                $logStmt = $pdo->prepare("
                    INSERT INTO audit_logs (admin_id, user_id, action, details) 
                    VALUES (?, ?, 'update_role', ?)
                ");
                $logStmt->execute([
                    $_SESSION['user']['id'],
                    $userId,
                    json_encode(['new_role' => $newRole])
                ]);
        
                $_SESSION['flash_message'] = 'Role updated successfully!';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error updating role: ' . $e->getMessage();
            }
        }    

        // Block or unblock user
        if (isset($_POST['block'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
                $stmt->execute([$userId]);
        
                // Ajouter l'action dans l'audit_logs
                $logStmt = $pdo->prepare("
                    INSERT INTO audit_logs (admin_id, user_id, action, details) 
                    VALUES (?, ?, 'block', NULL)
                ");
                $logStmt->execute([$_SESSION['user']['id'], $userId]);
        
                $_SESSION['flash_message'] = 'User blocked successfully!';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error blocking user: ' . $e->getMessage();
            }
        } elseif (isset($_POST['unblock'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_blocked = 0 WHERE id = ?");
                $stmt->execute([$userId]);
        
                // Ajouter l'action dans l'audit_logs
                $logStmt = $pdo->prepare("
                    INSERT INTO audit_logs (admin_id, user_id, action, details) 
                    VALUES (?, ?, 'unblock', NULL)
                ");
                $logStmt->execute([$_SESSION['user']['id'], $userId]);
        
                $_SESSION['flash_message'] = 'User unblocked successfully!';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error unblocking user: ' . $e->getMessage();
            }
        }
        
        header('Location: /?page=users');
        exit;
    }

    // Pagination logic
    $itemsPerPage = 10; // Number of users per page
    $pageNumber = isset($_GET['page_number']) ? max(1, intval($_GET['page_number'])) : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    // Fetch users with pagination
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, role, is_blocked FROM users LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch total number of users for pagination
        $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
        $totalUsers = $countStmt->fetchColumn();
        $totalPages = ceil($totalUsers / $itemsPerPage);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching users: ' . $e->getMessage();
        $users = [];
        $totalPages = 1;
    }

    // Fetch statistics for users
    if ($pageNumber === 1) { // Afficher uniquement sur la première page
        try {
            $totalUsers = $pdo->query("SELECT COUNT(*) AS count FROM users")->fetchColumn();
            $totalAdmins = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetchColumn();
            $totalUsersRole = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetchColumn();
            $totalGuests = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'guest'")->fetchColumn();
            $totalBlocked = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE is_blocked = 1")->fetchColumn();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error fetching statistics: ' . $e->getMessage();
        }
    }

    // Gérer le tri
    $validSortColumns = ['name', 'email', 'role'];
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $validSortColumns) ? $_GET['sort'] : 'name';
    $order = isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc']) ? $_GET['order'] : 'asc';

    // Fetch users avec tri et pagination
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, email, role, is_blocked 
            FROM users 
            ORDER BY $sort $order 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching users: ' . $e->getMessage();
        $users = [];
    }

?>

<head>
    <title>User Management</title>
</head>


    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <h1 class="text-3xl font-bold text-center mb-8">User Management</h1>

        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>


        <?php if ($pageNumber === 1): ?>
            <div class="bg-gray-100 shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">User Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-center"> <!-- 2 colonnes -->
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-xl font-semibold"><?= htmlspecialchars($totalUsers) ?></p>
                        <p class="text-gray-600">Total Users</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-xl font-semibold"><?= htmlspecialchars($totalAdmins) ?></p>
                        <p class="text-gray-600">Administrators</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-xl font-semibold"><?= htmlspecialchars($totalUsersRole) ?></p>
                        <p class="text-gray-600">Users</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <p class="text-xl font-semibold"><?= htmlspecialchars($totalBlocked) ?></p>
                        <p class="text-gray-600">Blocked Users</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>



        <!-- User Table -->
        <div class="bg-white shadow rounded-lg p-6">
            <table class="min-w-full bg-white border-collapse border border-gray-200">
                <thead>
                    <tr>
                        <th class="border border-gray-200 px-4 py-2 text-left text-gray-800">
                            <a href="?page=users&sort=name&order=<?= $sort === 'name' && $order === 'asc' ? 'desc' : 'asc' ?>">
                                Name <?= $sort === 'name' ? ($order === 'asc' ? '▲' : '▼') : '' ?>
                            </a>
                        </th>
                        <th class="border border-gray-200 px-4 py-2 text-left text-gray-800">
                            <a href="?page=users&sort=email&order=<?= $sort === 'email' && $order === 'asc' ? 'desc' : 'asc' ?>">
                                Email <?= $sort === 'email' ? ($order === 'asc' ? '▲' : '▼') : '' ?>
                            </a>
                        </th>
                        <th class="border border-gray-200 px-4 py-2 text-left text-gray-800">
                            <a href="?page=users&sort=role&order=<?= $sort === 'role' && $order === 'asc' ? 'desc' : 'asc' ?>">
                                Role <?= $sort === 'role' ? ($order === 'asc' ? '▲' : '▼') : '' ?>
                            </a>
                        </th>
                        <th class="border border-gray-200 px-4 py-2 text-left text-gray-800">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="<?= $user['is_blocked'] ? 'bg-red-100' : '' ?>">
                            <td class="border border-gray-200 px-4 py-2">
                                <?= htmlspecialchars($user['name']) ?>
                                <?php if ($user['is_blocked']): ?>
                                    <span class="text-xs text-red-500 font-bold">(Blocked)</span>
                                <?php endif; ?>
                            </td>
                            <td class="border border-gray-200 px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="border border-gray-200 px-4 py-2"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="border border-gray-200 px-4 py-2">
                                <div class="flex items-center space-x-4">
                                    <form method="POST" action="">
                                        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                                            <select name="role" class="border rounded px-2 py-1">
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                                                Update
                                            </button>
                                        </div>
                                    </form>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" action="">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                            <?php if ($user['is_blocked']): ?>
                                                <button type="submit" name="unblock" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700">
                                                    Unblock
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="block" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">
                                                    Block
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex justify-center space-x-2">
            <?php if ($pageNumber > 1): ?>
                <a href="?page=users&page_number=<?= $pageNumber - 1 ?>&sort=<?= $sort ?>&order=<?= $order ?>" 
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Previous
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=users&page_number=<?= $i ?>&sort=<?= $sort ?>&order=<?= $order ?>" 
                class="px-4 py-2 rounded <?= $i === $pageNumber ? 'bg-blue-700 text-white' : 'bg-gray-200 text-blue-500 hover:bg-gray-300' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($pageNumber < $totalPages): ?>
                <a href="?page=users&page_number=<?= $pageNumber + 1 ?>&sort=<?= $sort ?>&order=<?= $order ?>" 
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Next
                </a>
            <?php endif; ?>
        </div>
        
    </div>



<?php
include '../includes/footer.php';
?>
