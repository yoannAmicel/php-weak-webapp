<!DOCTYPE html>
<html lang="fr">

<?php 
    include_once '../functions/routes.php'; 
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($pdo)) {
        die('Erreur : connexion à la base de données non définie.');
    }

    if (isset($_SESSION['user']['id'])) {
        $userId = $_SESSION['user']['id'];
        $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result && !empty($result['profile_picture'])) {
            $profilePicture = htmlspecialchars($result['profile_picture']);
        }
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

            <!-- Navigation et bouton utilisateur -->
            <div class="flex items-center space-x-8">
                <nav class="flex space-x-8">
                    <a href="<?= route('home') ?>" class="hover:text-gray-400">Home</a>
                    <a href="<?= route('software') ?>" class="hover:text-gray-400">Software</a>
                    <a href="<?= route('news') ?>" class="hover:text-gray-400">News</a>
                    <a href="<?= route('contact') ?>" class="hover:text-gray-400">Contact</a>
                </nav>

                <?php if (isset($_SESSION['user'])): ?>
                    <!-- Bouton utilisateur -->
                    <div class="relative group">
                        <!-- Bouton utilisateur -->
                        <button class="flex items-center focus:outline-none">
                            <img src="<?= $profilePicture; ?>" alt="Profile Picture" 
                                class="h-10 w-10 rounded-full">
                        </button>

                        <!-- Sous-menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 hidden group-hover:block group-focus-within:block">
                            <a href="<?= route('myaccount') ?>" 
                            class="block px-4 py-2 text-gray-800 hover:bg-gray-100 hover:text-gray-900">
                                My account
                            </a>
                            <form method="POST" action="/?action=logout">
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-100 hover:text-red-700 font-semibold">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
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


