<!DOCTYPE html>
<html lang="fr">

<?php
    include '../includes/header.php';
    require_once '../config/config.php';
    global $pdo;

    // Vérification de la session utilisateur
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifie si l'utilisateur est admin
    if (!isset($_SESSION['user']) || !hasPermission('admin')) {
        header('Location: /?page=login');
        exit;
    }

    // Gestion des actions d'approbation/rejet
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $news_id = intval($_POST['news_id'] ?? 0);

        if (isset($_POST['approve'])) {
            try {
                $stmt = $pdo->prepare("UPDATE news SET status = 'approved' WHERE id = ?");
                $stmt->execute([$news_id]);
                $_SESSION['flash_message'] = 'News approved successfully.';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error approving news: ' . $e->getMessage();
            }
        } elseif (isset($_POST['reject'])) {
            try {
                // Récupérer les informations de la news (y compris l'image)
                $stmt = $pdo->prepare("SELECT image_url FROM news WHERE id = ?");
                $stmt->execute([$news_id]);
                $news = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($news && !empty($news['image_url']) && $news['image_url'] !== '/img/news/default.jpg') {
                    $imagePath = __DIR__ . '/../public' . $news['image_url'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath); // Supprime l'image du serveur
                    }
                }

                // Supprimer la news après suppression de l'image
                $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$news_id]);

                $_SESSION['flash_message'] = 'News rejected and deleted successfully.';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error rejecting news: ' . $e->getMessage();
            }
        }

        header('Location: /?page=validation');
        exit;
    }

    // Récupérer les news en attente de validation
    try {
        $stmt = $pdo->query("SELECT * FROM news WHERE status = 'pending'");
        $pendingNews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching pending news.';
        $pendingNews = [];
    }
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../img/Logo.png">
    <link rel="stylesheet" href="/css/main.css">
    <title>News Validation</title>
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <h1 class="text-3xl font-bold text-center mb-8">News Validation</h1>

        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <div class="bg-green-100 border border-green-500 text-green-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-500 text-red-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Liste des news en attente -->
        <div class="grid gap-8">
            <?php if (!empty($pendingNews)): ?>
                <?php foreach ($pendingNews as $news): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <img src="<?= htmlspecialchars($news['image_url'] ?? '/img/news/default.jpg') ?>" 
                             alt="<?= htmlspecialchars($news['title'] ?? 'No Title') ?>" 
                             class="w-full h-64 object-cover">
                        <div class="p-6">
                            <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($news['title'] ?? 'No Title') ?></h2>
                            <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($news['published_date'] ?? 'Unknown Date') ?></p>
                            <p class="text-gray-700 mb-4"><?= htmlspecialchars($news['content'] ?? 'No Content Available') ?></p>
                            <form method="POST" class="flex space-x-4">
                                <input type="hidden" name="news_id" value="<?= htmlspecialchars($news['id']) ?>">
                                <button type="submit" name="approve" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                    Approve
                                </button>
                                <button type="submit" name="reject" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500">No news pending validation.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
