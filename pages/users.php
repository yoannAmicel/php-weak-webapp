<!DOCTYPE html>


<?php
    include '../includes/header.php';
    include_once '../includes/users_helper.php';
?>


<head>
    <title>User Management</title>
</head>


    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <h1 class="text-3xl font-bold text-center mb-8">User Management</h1>



        <!----------------------------------------------------------------------------------->
        <!-- Affichage des messages de succès et d'erreur ----------------------------------->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); // Suppression du message après affichage ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); // Suppression du message après affichage ?>
        <?php endif; ?>
        <!----------------------------------------------------------------------------------->
        


        <!-- Affichage des statistiques utilisateur uniquement sur la première page -->
        <?php if ($pageNumber === 1): ?>
            <div class="bg-gray-100 shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">User Statistics</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-center">
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


        <!-- Tableau des utilisateurs -->
        <div class="bg-white shadow rounded-lg p-6">
            <table class="min-w-full bg-white border-collapse border border-gray-200">
                <thead>
                    <tr>
                        <!-- Colonnes avec tri possible -->
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
                            <!-- Nom de l'utilisateur -->
                            <td class="border border-gray-200 px-4 py-2">
                                <?= htmlspecialchars($user['name']) ?>
                                <?php if ($user['is_blocked']): ?>
                                    <span class="text-xs text-red-500 font-bold">(Blocked)</span>
                                <?php endif; ?>
                            </td>
                            <!-- Email de l'utilisateur -->
                            <td class="border border-gray-200 px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
                            <!-- Rôle de l'utilisateur -->
                            <td class="border border-gray-200 px-4 py-2"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="border border-gray-200 px-4 py-2">
                                <div class="flex items-center space-x-4">
                                    <!-- Formulaire pour changer le rôle d'un utilisateur -->
                                    <form method="POST" action="?action=users.submit">
                                        
                                        <!-- Protection CSRF : Inclusion du token pour vérifier l'authenticité de la requête -->
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
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
                                    <!-- Boutons de blocage / déblocage -->
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" action="?action=users.submit">

                                            <!-- Protection CSRF : Inclusion du token pour vérifier l'authenticité de la requête -->
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

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
