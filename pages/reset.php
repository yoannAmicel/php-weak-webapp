<!DOCTYPE html>

<?php
    include '../includes/header.php';
    include_once '../includes/reset_helper.php';
?>


<head>
    <title>Reset Password</title>
</head>


    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg mt-20 mb-12">



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



            <!-- Titre principal de la page -->
            <h2 class="text-2xl font-bold mb-4">Reset Password</h2>

            <!-- Instructions pour l'utilisateur -->
            <p class="text-sm text-gray-600 mb-4">Please enter your new password below.</p>

            <!-- Formulaire de réinitialisation du mot de passe -->
            <form method="POST" action="">
                
                <!-- Champ "Nouveau mot de passe" -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input id="password" type="password" name="password" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                                focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Champ "Confirmation du mot de passe" -->
                <div class="mb-4">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input id="confirm_password" type="password" name="confirm_password" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                                focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Bouton de validation du formulaire -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm 
                                text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 
                                focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset Password
                    </button>
                </div>

            </form>

            <!-- Lien pour retourner à la page de connexion -->
            <div class="mt-6 text-center">
                <a href="/pages/login.php" class="text-indigo-600 hover:text-indigo-500 font-medium">Back to Login</a>
            </div>

        </div>
    </div>

<?php
    include '../includes/footer.php';
?>