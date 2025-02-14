<!DOCTYPE html>

<?php
    include_once '../includes/header.php';
    include_once '../includes/account_helper.php';
?>

<head>
    <title>My Account</title>
</head>


    <div class="w-full max-w-2xl mx-auto bg-white p-12 rounded-lg shadow-lg mt-16 mb-16">
        <h2 class="text-2xl font-bold mb-4">My Account</h2>



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



        <?php if (hasPermission('admin', $pdo) || hasPermission('user', $pdo)): ?>
            <form method="POST" enctype="multipart/form-data" action="?action=account.submit">

                <!-- S.Account.1 - Champ caché contenant le token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                <!-- Section "Général" -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 text-gray-600 border-b border-gray-300 pb-2">General</h3>

                    <!-- Nom -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-600">Name</label>
                        <input id="name" type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-600">Email Address</label>
                        <input id="email" type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Upload de la photo de profil -->
                    <div class="mb-4">
                        <label for="profile_picture" class="block text-sm font-medium text-gray-600">Profile Picture</label>
                        <input id="profile_picture" type="file" name="profile_picture"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                        <!-- Affichage de la photo actuelle -->
                        <div class="mt-4 flex justify-center">
                            <img src="<?= htmlspecialchars($user['profile_picture']); ?>" alt="Current Profile Picture"
                                class="h-60 w-60 rounded-full object-cover shadow-md">
                        </div>

                        <!-- Bouton pour supprimer la photo -->
                        <?php if ($user['profile_picture'] !== '/img/users/everyone.png'): ?>
                            <div class="mt-4 flex justify-center">
                                <button type="submit" name="delete_profile_picture" value="1"
                                    class="py-2 px-4 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                                    Remove Profile Picture
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section "Sécurité" -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 text-gray-600 border-b border-gray-300 pb-2">Security</h3>
                    <div class="mb-4">
                        <label for="new_password" class="block text-sm font-medium text-gray-600">New Password</label>
                        <input id="new_password" type="password" name="new_password"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-600">Confirm New Password</label>
                        <input id="confirm_password" type="password" name="confirm_password"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Bouton de sauvegarde -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm 
                            text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 
                            focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Changes
                    </button>
                </div>
            </form>

            <!-- Bouton de suppression avec un événement onclick -->
            <div class="mt-4 flex justify-center">
                <button type="button"
                    onclick="openDeleteConfirmation()"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm 
                            text-sm font-medium text-white bg-red-600 hover:bg-red-700 
                            focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Delete my account
                </button>
            </div>
        <?php endif; ?>

    </div>


<!-- Popup de confirmation -->
<div id="delete-confirmation-popup" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
        <h3 class="text-xl font-bold mb-4 text-red-600">Confirm Account Deletion</h3>
        <p class="mb-6 text-gray-700">
            Are you sure you want to delete your account? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-4">
            <!-- Bouton Annuler -->
            <button type="button"
                onclick="closeDeleteConfirmation()"
                class="py-2 px-4 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                Cancel
            </button>
            <!-- Bouton Confirmer -->
            <form method="POST" action="?action=account.submit">

                <!-- S.Account.1 - Champ caché contenant le token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <button type="submit" name="delete_account" value="1"
                    class="py-2 px-4 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Confirm
                </button>
            </form>
        </div>
    </div>
</div>

<script src="/js/myaccount.js"></script>

<?php
include '../includes/footer.php';
?>
