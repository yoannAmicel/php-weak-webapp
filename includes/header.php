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

    <style>
        /* Pour que le footer colle au bas de page */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        footer {
            margin-top: auto; 
            background-color: #e5e7eb; 
            padding: 1.5rem 0;
        }

        /* Pour la partie News */
        .truncate {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical; 
            overflow: hidden;
        }

        .comments-section {
            display: none;
            margin-top: 1rem;
            border-top: 1px solid #ddd;
            padding-top: 1rem;
        }

        .add-comment-form {
            display: none;
            margin-top: 1rem;
        }

        .center-button {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
    </style>

    <script>
        // Bouton permettant l'affichage de toute la description & commentaires
        function toggleContent(element) {
            const content = element.previousElementSibling;
            const commentsSection = element.nextElementSibling;
            if (content.classList.contains('truncate')) {
                content.classList.remove('truncate');
                element.textContent = '<- less info';
                commentsSection.style.display = 'block';
            } else {
                content.classList.add('truncate');
                element.textContent = 'More info ->';
                commentsSection.style.display = 'none';
            }
        }

        // Bouton permettant d'afficher le formulaire de commentaire
        function toggleAddCommentForm(button) {
            const form = button.parentElement.nextElementSibling; 
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
                button.textContent = 'Hide Comment Form';
            } else {
                form.style.display = 'none';
                button.textContent = 'Add a Comment';
            }
        }
    </script>
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


    <div class="mb-16"></div>
</body>
</html>
