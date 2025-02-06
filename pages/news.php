<!DOCTYPE html>

<?php
    include '../includes/header.php';
    require_once '../config/config.php'; // Inclusion correcte du fichier de configuration
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si la connexion PDO est définie
    if (!isset($pdo)) {
        die('Erreur : connexion à la base de données non définie.');
    }

    // Récupérer les données de la table news
    try {
        $query = $pdo->query("SELECT * FROM news");
        $newsItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des données : ' . $e->getMessage());
    }
?>

<head>
    <title>News</title>
</head>

<div class="container mx-auto px-4 py-8 max-w-5xl">
    <h1 class="text-3xl font-bold text-center mb-8">NEWS</h1>

    <div class="grid gap-12">
        <?php foreach ($newsItems as $news): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="<?= htmlspecialchars($news['image_url'] ?? 'img/default.jpg') ?>" 
                     alt="<?= htmlspecialchars($news['title'] ?? 'No Title') ?>" 
                     class="w-full h-64 object-cover">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($news['title'] ?? 'No Title') ?></h2>
                    <div class="text-sm text-gray-500 mb-4 flex items-center space-x-4">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8h-6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2h-2z" />
                            </svg>
                            <?= htmlspecialchars($news['source'] ?? 'Unknown Source') ?>
                        </span>
                        <span>
                            <?= isset($news['published_date']) ? date("d M, Y", strtotime($news['published_date'])) : 'No Date' ?>
                        </span>
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553 2.276A2 2 0 0120 14.118V18a2 2 0 01-2 2H6a2 2 0 01-2-2v-3.882a2 2 0 01.447-1.342L9 10m3 6v-6m0 0L9 7m3 3l3-3" />
                            </svg>
                            <?= htmlspecialchars($news['comments_count'] ?? 0) ?> Comments
                        </span>
                    </div>
                    <p class="text-gray-700 mb-4">
                        <?= htmlspecialchars($news['content'] ?? 'No Content Available') ?>
                    </p>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 font-bold">Read More →</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
    include '../includes/footer.php';
?>
