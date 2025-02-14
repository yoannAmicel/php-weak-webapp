<!DOCTYPE html>

<?php
    include '../includes/header.php';
?>


<head>
    <title>Register</title>
</head>


    <div class="w-full max-w-2xl mx-auto bg-white p-12 rounded-lg shadow-lg mt-18 mb-18">
        <h2 class="text-2xl font-bold mb-4">Register</h2>



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




        <!-- Formulaire d'inscription -->
        <form method="POST" action="?action=register.submit">

            <!-- S.Register.2 - Protection CSRF : Inclusion du token pour vérifier l'authenticité de la requête -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <!-- Champ "Nom" -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input id="name" type="text" name="name" 
                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Champ "Email" -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input id="email" type="email" name="email" 
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Champ "Mot de passe" -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <!-- Message d'erreur si le mot de passe ne respecte pas les critères -->
            </div>

            <!-- Champ "Confirmation du mot de passe" -->
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                            focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Bouton pour soumettre le formulaire -->
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm 
                            text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 
                            focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Register
                </button>
            </div>
        </form>

        <!-- Lien vers la page de connexion pour les utilisateurs déjà inscrits -->
        <div class="mt-6 text-center">
            <span class="text-sm text-gray-600">Already have an account?</span>
            <a href="<?= route('login') ?>" class="ml-2 text-indigo-600 hover:text-indigo-500 font-medium">Log in</a>
        </div>

    </div>


<?php
    include '../includes/footer.php';
?>

