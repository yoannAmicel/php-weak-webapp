<!DOCTYPE html>

<?php
    include_once '../includes/validation_helper.php';
    require '../includes/header.php';
?>


<head>
    <title>News Validation</title>
</head>


    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <!-- Titre principal de la page -->
        <h1 class="text-3xl font-bold text-center mb-8">News Validation</h1>



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



        <!-- Liste des news en attente de validation -->
        <div class="grid gap-8">
            <?php if (!empty($pendingNews)): ?>
                <?php foreach ($pendingNews as $news): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <!-- Image associée à la news avec une image par défaut si absente -->
                        <img src="<?= htmlspecialchars($news['image_url'] ?? '/img/news/default.jpg') ?>" 
                            alt="<?= htmlspecialchars($news['title'] ?? 'No Title') ?>" 
                            class="w-full h-64 object-cover">
                        
                        <div class="p-6">
                            <!-- Titre de l'article -->
                            <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($news['title'] ?? 'No Title') ?></h2>
                            <!-- Date de publication (ou texte par défaut si non définie) -->
                            <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($news['published_date'] ?? 'Unknown Date') ?></p>
                            <!-- Contenu de la news -->
                            <p class="text-gray-700 mb-4"><?= htmlspecialchars($news['content'] ?? 'No Content Available') ?></p>

                            <!-- Formulaire d'approbation/rejet de la news -->
                            <form method="POST" action="?action=validation.submit" class="flex space-x-4">

                                <!-- Protection CSRF : Inclusion du token pour vérifier l'authenticité de la requête -->
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                <input type="hidden" name="news_id" value="<?= htmlspecialchars($news['id']) ?>">
                                <!-- Bouton pour approuver la news -->
                                <button type="submit" name="approve" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                    Approve
                                </button>
                                <!-- Bouton pour rejeter la news -->
                                <button type="submit" name="reject" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <!-- Message affiché si aucune news en attente -->
                <p class="text-center text-gray-500">No news pending validation.</p>
            <?php endif; ?>
            
        </div>
    </div>


<?php
    include '../includes/footer.php';
?>
