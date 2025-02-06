<!DOCTYPE html>

<?php
    include '../includes/header.php';
    require_once '../config/config.php'; // Inclusion du fichier de configuration
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si la connexion PDO est définie
    if (!isset($pdo)) {
        die('Erreur : connexion à la base de données non définie.');
    }

    // Récupérer les données de la table software
    try {
        $query = $pdo->query("SELECT * FROM software");
        $softwareItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des données : ' . $e->getMessage());
    }
?>

<head>
    <title>Software</title>
</head>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">SOFTWARE</h1>

    <!-- Liste des logiciels -->
    <div class="grid gap-6">
        <?php foreach ($softwareItems as $software): ?>
            <div class="bg-white rounded-lg shadow-md p-6 flex justify-between items-center">
                <div class="flex-1 mr-4">
                    <h2 class="text-xl font-bold" style="color: <?= htmlspecialchars($software['title_color']) ?>">
                        <?= htmlspecialchars($software['name']) ?>
                    </h2>
                    <p class="text-gray-600">
                        <?= htmlspecialchars($software['description']) ?>
                    </p>
                </div>
                <a href="<?= htmlspecialchars($software['more_info_url']) ?>" 
                   class="flex-shrink-0 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center">
                    More info
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
    include '../includes/footer.php';
?>
