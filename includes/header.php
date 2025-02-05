<!DOCTYPE html>
<html lang="fr">

<?php include_once '../functions/routes.php'; ?>

<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/Logo.png">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 text-gray-900">
    <!-- Header -->
    <header class="bg-gray-800 text-white py-4 shadow-md fixed top-0 left-0 w-full z-50">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Logo et nom -->
            <a href="<?= route('home') ?>" class="flex items-center space-x-2 text-2xl font-bold">
                <img src="../img/Logo.png" alt="Logo" class="h-8 w-8">
                <span>Avenix</span>
            </a>

            <!-- Navigation et bouton de connexion/déconnexion -->
            <div class="flex items-center space-x-8">
                <nav class="flex space-x-8">
                    <a href="<?= route('home') ?>" class="hover:text-gray-400">Home</a>
                    <a href="<?= route('software') ?>" class="hover:text-gray-400">Software</a>
                    <a href="<?= route('news') ?>" class="hover:text-gray-400">News</a>
                    <a href="<?= route('contact') ?>" class="hover:text-gray-400">Contact</a>
                </nav>

                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Formulaire pour déconnexion -->
                    <form method="POST" action="/?action=logout" style="display: inline;">
                        <button type="submit" 
                                class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700 transition duration-300">
                            Logout
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Bouton de connexion -->
                    <a href="<?= route('login') ?>" 
                    class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                        Connexion
                    </a>
                <?php endif; ?>


            </div>
        </div>
    </header>

    <div class="h-8"></div>
    <div class="h-8"></div>
</body>
</html>
