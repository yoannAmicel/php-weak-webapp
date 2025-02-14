<!DOCTYPE html>

<?php
    include '../includes/header.php';
    include '../includes/forgot_helper.php';
?>


<head>
    <title>Forgot Password</title>
</head>


    <div class="container mx-auto px-4 py-8">
        <!-- Conteneur principal du formulaire de réinitialisation de mot de passe -->
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg mt-20 mb-12">
            
            <!-- Titre principal de la page -->
            <h2 class="text-2xl font-bold mb-4">Forgot Password</h2>


            <!----------------------------------------------------------------------------------->
            <!-- Affichage des messages de succès et d'erreur ----------------------------------->
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded mb-4 w-full">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); // Suppression du message après affichage ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4 w-full">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); // Suppression du message après affichage ?>
            <?php endif; ?>
            <!----------------------------------------------------------------------------------->




            <!-- Texte d'explication pour guider l'utilisateur sur la procédure -->
            <p class="text-sm text-gray-600 mb-4">
                Enter your email address below, and we will send you a link to reset your password.
            </p>

            <!-- Formulaire de réinitialisation du mot de passe -->
            <form method="POST" action="?action=password_reset.submit">

                <!-- S.Forgot.2 - Protection CSRF : Inclusion du token pour vérifier l'authenticité de la requête -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <!-- Champ pour saisir l'adresse e-mail -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input id="email" type="email" name="email" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                        focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                    <!-- Affichage d'un message d'erreur si l'email est invalide ou manquant -->
                    <?php if (!empty($errors['email'])): ?>
                        <span class="text-red-500 text-sm"><?= htmlspecialchars($errors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Bouton pour soumettre le formulaire et demander la réinitialisation du mot de passe -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm 
                        text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 
                        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Send Reset Link
                    </button>
                </div>
            </form>

            <!-- Lien pour retourner à la page de connexion -->
            <div class="mt-6 text-center">
                <a href="<?= route('login') ?>" class="text-indigo-600 hover:text-indigo-500 font-medium">
                    Back to Login
                </a>
            </div>

        </div>

    </div>
    

<?php
    include '../includes/footer.php';
?>

