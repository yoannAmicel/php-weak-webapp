<!DOCTYPE html>

<?php
    include '../includes/header.php';
    include_once '../includes/login_helper.php';
?>


<head>
    <title>Login</title>
</head>


    <div class="w-full max-w-2xl mx-auto bg-white p-12 rounded-lg shadow-lg mt-28">
        <h2 class="text-2xl font-bold mb-4">Login</h2>



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



        <!-- Formulaire de connexion -->
        <form method="POST" action="?action=login.submit">

            <!-- S.Login.4 - Protection CSRF : Inclusion du token pour vérifier l'authenticité de la requête -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">


            <!-- Champ d'entrée pour l'adresse e-mail -->
            <!-- S.Login.5 - Protection contre l'autocompletion avec autocomplete=off-->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input id="email" type="email" name="email"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                    focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                
                <!-- Message d'erreur si l'e-mail est invalide -->
                <?php if (!empty($errors['email'])): ?>
                    <span class="text-red-500 text-sm"><?= htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Champ d'entrée pour le mot de passe -->
            <!-- S.Login.5 - Protection contre l'autocompletion avec autocomplete=off-->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" name="password" autocomplete="off" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                    focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                <!-- Message d'erreur si le mot de passe est invalide -->
                <?php if (!empty($errors['password'])): ?>
                    <span class="text-red-500 text-sm"><?= htmlspecialchars($errors['password']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Option "Se souvenir de moi" et lien de réinitialisation du mot de passe -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                </div>
                <a class="text-sm text-indigo-600 hover:text-indigo-500" href="<?= route('forgot-password') ?>">
                    Forgot your password?
                </a>
            </div>

            <!-- Bouton de connexion -->
            <div>
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm 
                    text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 
                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Log in
                </button>
            </div>
        </form>

        <!-- Lien vers la création de compte -->
        <div class="mt-6 text-center">
            <span class="text-sm text-gray-600">Don't have an account?</span>
            <a href="<?= route('register') ?>" 
                class="ml-2 text-indigo-600 hover:text-indigo-500 font-medium">
                Create Account
            </a>
        </div>

    </div>

<?php
    include '../includes/footer.php';
?>
