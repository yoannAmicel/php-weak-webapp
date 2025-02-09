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
            <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded">
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



        <?php if (hasPermission('admin') || hasPermission('user')): ?>
            <!-- Formulaire de modification du profil (accessible aux administrateurs et aux utilisateurs) -->
            <form method="POST" enctype="multipart/form-data">

                <!-- Section "Général" : Modification des informations de base -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 text-gray-600 border-b border-gray-300 pb-2">General</h3>

                    <!-- Champ de modification du nom -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-600">Name</label>
                        <input id="name" type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Champ de modification de l'adresse e-mail -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-600">Email Address</label>
                        <input id="email" type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Champ d'upload pour la photo de profil -->
                    <div class="mb-4">
                        <label for="profile_picture" class="block text-sm font-medium text-gray-600">Profile Picture</label>
                        <input id="profile_picture" type="file" name="profile_picture"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                        <!-- Affichage de la photo de profil actuelle -->
                        <div class="mt-4 flex justify-center">
                            <img src="<?= htmlspecialchars($user['profile_picture']); ?>" alt="Current Profile Picture"
                                class="h-60 w-60 rounded-full object-cover shadow-md">
                        </div>

                        <!-- Bouton pour supprimer la photo de profil, sauf si c'est l'avatar par défaut -->
                        <?php if ($user['profile_picture'] !== '/img/users/everyone.png'): ?>
                            <div class="mt-4 flex justify-center">
                                <form method="POST">
                                    <button type="submit" name="delete_profile_picture"
                                        class="py-2 px-4 bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none">
                                        Remove Profile Picture
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section "Sécurité" : Modification du mot de passe -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 text-gray-600 border-b border-gray-300 pb-2">Security</h3>

                    <!-- Champ pour entrer un nouveau mot de passe -->
                    <div class="mb-4">
                        <label for="new_password" class="block text-sm font-medium text-gray-600">New Password</label>
                        <input id="new_password" type="password" name="new_password"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Champ pour confirmer le nouveau mot de passe -->
                    <div class="mb-4">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-600">Confirm New Password</label>
                        <input id="confirm_password" type="password" name="confirm_password"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Bouton pour sauvegarder les modifications -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm 
                            text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 
                            focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Changes
                    </button>
                </div>
            </form>
        <?php endif; ?>

    </div>


<?php
include '../includes/footer.php';
?>
