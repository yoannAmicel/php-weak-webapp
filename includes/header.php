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

    $isAdmin = false;

    if (isset($_SESSION['user']['id'])) {
        $userId = $_SESSION['user']['id'];
        $stmt = $pdo->prepare("SELECT profile_picture, role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            $profilePicture = htmlspecialchars($result['profile_picture'] ?? '');
            $isAdmin = $result['role'] === 'admin'; // Vérifie si le rôle est admin
        }
    }

    // Récupérer le nombre de news en attente de validation
    $pendingCount = 0;
    if (isset($_SESSION['user']) && hasPermission('admin')) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) AS pending_count FROM news WHERE status = 'pending'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $pendingCount = $result['pending_count'] ?? 0;
        } catch (PDOException $e) {
            $pendingCount = 0; // En cas d'erreur, on affiche 0
        }
    }
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/Logo.png">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="/css/main.css">
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
                    <!-- Remplace Contact par Validations si l'utilisateur est admin -->
                    <?php if (isset($_SESSION['user']) && hasPermission('admin')): ?>
                        <a href="<?= route('validation') ?>" class="relative hover:text-gray-400">
                            Validations
                            <?php if ($pendingCount > 0): ?>
                                <span class="absolute -top-3 -right-2 bg-red-600 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                                    <?= htmlspecialchars($pendingCount) ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= route('contact') ?>" class="hover:text-gray-400">Contact</a>
                    <?php endif; ?>
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
                            <?php if (isset($_SESSION['user']) && hasPermission('admin')): ?>
                                <a href="<?= route('users') ?>" 
                                class="block px-4 py-2 text-gray-800 hover:bg-gray-100 hover:text-gray-900">
                                    User Management
                                </a>
                            <?php endif; ?>
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
