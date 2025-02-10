<!DOCTYPE html>

<?php
    include '../includes/header.php';
    include_once '../includes/news_helper.php';
?>


<head>
    <title>News</title>
</head>


    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <h1 class="text-3xl font-bold text-center mb-8">NEWS</h1>



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




        <?php if (isset($_SESSION['user']) && (hasPermission('user', $pdo) || hasPermission('admin', $pdo))): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden relative p-6 w-full mx-auto mb-8">
                <!-- Section Ajout d'une News -->
                <h2 class="text-2xl font-bold mb-4">Add News</h2>
                <form method="POST" enctype="multipart/form-data" action="?action=news.submit">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <!-- Section Image -->
                        <div class="col-span-2">
                            <label for="image" class="block font-bold text-gray-700 mb-2">Upload Image:</label>
                            <input type="file" id="image" name="image" 
                                accept="image/jpeg, image/png, image/gif"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Section Titre -->
                        <div class="col-span-2">
                            <label for="title" class="block font-bold text-gray-700 mb-2">Title:</label>
                            <input type="text" id="title" name="title" 
                                placeholder="Enter the news title"
                                class="text-xl font-bold mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>

                        <!-- Software et Published Date -->
                        <div class="col-span-1">
                            <label for="software" class="block font-bold text-gray-700 mb-2">Software:</label>
                            <select id="software" name="software" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <?php foreach ($softwareItems as $software): ?>
                                    <option value="<?= htmlspecialchars(ltrim($software['name'], '#')) ?>" 
                                        style="background-color: <?= htmlspecialchars($software['title_color']) ?>; color: #ffffff;">
                                        <?= htmlspecialchars(ltrim($software['name'], '#')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label for="published_date" class="block font-bold text-gray-700 mb-2">Published Date:</label>
                            <input type="date" id="published_date" name="published_date" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Section Contenu -->
                        <div class="col-span-2">
                            <label for="content" class="block font-bold text-gray-700 mb-2">Content:</label>
                            <textarea id="content" name="content" rows="4"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Enter the news content" required></textarea>
                        </div>
                    </div>

                    <!-- Bouton Submit -->
                    <div class="text-center mt-4">
                        <button type="submit" name="add_news"
                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Add
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>



        <div class="grid gap-12">
            <?php foreach ($newsItems as $news): ?>
                <!-- Conteneur principal d'une news -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden relative">

                    <!-- Image associée à la news -->
                    <img src="<?= htmlspecialchars($news['image_url'] ?? 'img/default.jpg') ?>" 
                        alt="<?= htmlspecialchars($news['title'] ?? 'No Title') ?>" 
                        class="w-full h-64 object-cover">

                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <!-- Titre de la news -->
                            <h2 class="text-xl font-bold mb-2">
                                <?= htmlspecialchars($news['title'] ?? 'No Title') ?>
                            </h2>

                            <!-- Bouton de suppression visible UNIQUEMENT pour les administrateurs -->
                            <?php if (isset($_SESSION['user']) && hasPermission('admin', $pdo)): ?>
                                <button type="button" 
                                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"
                                        onclick="openNewsPopup(<?= htmlspecialchars($news['id']) ?>, '<?= htmlspecialchars($news['title']) ?>')">
                                    Delete
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Informations sur la source, la date de publication et le nombre de commentaires -->
                        <div class="text-sm text-gray-500 mb-4 flex items-center space-x-4">
                            <span class="flex items-center">
                                <!-- Icone source -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8h-6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2h-2z" />
                                </svg>
                                <?= htmlspecialchars($news['source'] ?? 'Unknown Source') ?>
                            </span>
                            <span>
                                <?= isset($news['published_date']) ? date("d M, Y", strtotime($news['published_date'])) : 'No Date' ?>
                            </span>
                            <span class="flex items-center">
                                <!-- Icone commentaires -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553 2.276A2 2 0 0120 14.118V18a2 2 0 01-2 2H6a2 2 0 01-2-2v-3.882a2 2 0 01.447-1.342L9 10m3 6v-6m0 0L9 7m3 3l3-3" />
                                </svg>
                                <?= htmlspecialchars($news['comments_count']) ?> Comments
                            </span>
                        </div>

                        <!-- Contenu de la news -->
                        <p class="text-gray-700 mb-4 truncate">
                            <?= htmlspecialchars($news['content'] ?? 'No Content Available') ?>
                        </p>

                        <!-- Bouton pour afficher plus d'infos si le contenu est long ou s'il y a des commentaires -->
                        <?php if (strlen($news['content']) > 100 || $news['comments_count'] > 0): ?>
                            <button onclick="toggleContent(this)" class="text-indigo-600 hover:text-indigo-800 font-bold">More info →</button>
                        <?php endif; ?>

                        <!-- Affichage des commentaires si existants -->
                        <?php if ($news['comments_count'] > 0): ?>
                            <div class="comments-section">
                                <h3 class="text-lg font-semibold mb-2">Comments:</h3>
                                <?php foreach ($comments as $comment): ?>
                                    <?php if ($comment['news_id'] == $news['id']): ?>
                                        <div class="flex items-start mb-4">
                                            <!-- Photo de profil de l'auteur du commentaire -->
                                            <img src="<?= htmlspecialchars($comment['profile_picture'] ?? '/uploads/account.png') ?>" 
                                                alt="Profile Picture" 
                                                class="w-12 h-12 rounded-full mr-4">
                                            <div class="flex-grow">
                                                <p class="font-bold text-gray-800">
                                                    <?= htmlspecialchars($comment['username'] ?? 'Anonymous') ?>
                                                </p>
                                                <p class="text-gray-600">"<?= htmlspecialchars($comment['comment'] ?? '') ?>"</p>
                                                <p class="text-xs text-gray-400">Posted on <?= htmlspecialchars($comment['created_at'] ?? '') ?></p>
                                            </div>

                                            <!-- Bouton Delete visible UNIQUEMENT si l'utilisateur est connecté et A LES DROITS -->
                                            <?php if (isset($_SESSION['user']) && (hasPermission('admin', $pdo) || $_SESSION['user']['id'] == $comment['userID'])): ?>
                                                <form method="POST" action="?action=news.submit" class="ml-4">
                                                    <input type="hidden" name="delete_comment" value="1">
                                                    <input type="hidden" name="comment_id" value="<?= htmlspecialchars($comment['id']) ?>">
                                                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                                        Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>


                        <!-- Bouton pour afficher le formulaire d'ajout de commentaire -->
                        <div class="center-button">
                            <button onclick="toggleAddCommentForm(this)" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                Add a Comment
                            </button>
                        </div>


                        <!-- Formulaire pour ajouter un commentaire -->
                        <div class="add-comment-form bg-gray-100 p-4 rounded-lg">
                            <form method="POST" enctype="multipart/form-data" action="?action=news.submit">
                                <input type="hidden" name="news_id" value="<?= htmlspecialchars($news['id']) ?>">

                                <!-- Champ pour uploader une photo de profil (si l'utilisateur n'est pas connecté) -->
                                <?php if (!isset($_SESSION['user'])): ?>
                                    <div class="mb-4">
                                        <label for="profile_picture_<?= htmlspecialchars($news['id']) ?>" class="block text-gray-700 font-bold mb-1">Upload Profile Picture:</label>
                                        <input type="file" id="profile_picture_<?= htmlspecialchars($news['id']) ?>" name="profile_picture" class="border rounded w-full py-2 px-3">
                                    </div>
                                <?php endif; ?>

                                <!-- Champ nom (si l'utilisateur n'est pas connecté) -->
                                <?php if (!isset($_SESSION['user'])): ?>
                                    <div class="mb-4">
                                        <label for="name_<?= htmlspecialchars($news['id']) ?>" class="block text-gray-700 font-bold mb-1">Name:</label>
                                        <input type="text" id="name_<?= htmlspecialchars($news['id']) ?>" name="name" class="border rounded w-full py-2 px-3">
                                    </div>
                                <?php endif; ?>

                                <!-- Champ pour entrer le commentaire -->
                                <div class="mb-4">
                                    <label for="comment_<?= htmlspecialchars($news['id']) ?>" class="block text-gray-700 font-bold mb-1">Comment:</label>
                                    <textarea id="comment_<?= htmlspecialchars($news['id']) ?>" name="comment" class="border rounded w-full py-2 px-3" required></textarea>
                                </div>

                                <!-- Bouton pour soumettre le commentaire -->
                                <button type="submit" name="add_comment" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>



        <!-- Section de pagination -->
        <div class="mt-4 flex justify-center space-x-2">
            <!-- Bouton "Previous" : affiché uniquement si l'on n'est pas sur la première page -->
            <?php if ($pageNumber > 1): ?>
                <a href="?page=news&page_number=<?= $pageNumber - 1 ?>" 
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Previous
                </a>
            <?php endif; ?>

            <!-- Affichage des numéros de pages -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=news&page_number=<?= $i ?>" 
                class="px-4 py-2 rounded 
                <?= $i === $pageNumber ? 'bg-blue-700 text-white' : 'bg-gray-200 text-blue-500 hover:bg-gray-300' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- Bouton "Next" : affiché uniquement si l'on n'est pas sur la dernière page -->
            <?php if ($pageNumber < $totalPages): ?>
                <a href="?page=news&page_number=<?= $pageNumber + 1 ?>" 
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>



<!-- Popup de confirmation pour la SUPPRESSION d'une NEWS -->
<div id="delete-news-popup" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
        
        <!-- Titre du popup en rouge pour indiquer une action critique -->
        <h3 class="text-xl font-bold mb-4 text-red-600">Confirm Deletion</h3>

        <!-- Message indiquant le titre de la news qui va être supprimée -->
        <p id="popup-news-message" class="mb-6 text-gray-700"></p>

        <!-- Formulaire de confirmation de suppression -->
        <form id="delete-news-form" method="POST" action="?action=news.submit">
            <!-- Champ caché pour stocker l'ID de la news à supprimer -->
            <input type="hidden" id="delete-news-id" name="news_id" value="">

            <!-- Champ caché pour indiquer que l'on effectue une suppression -->
            <input type="hidden" name="delete_news" value="1">

            <!-- Boutons de validation et d'annulation -->
            <div class="flex justify-end space-x-4">
                <!-- Bouton pour annuler l'action et fermer le popup -->
                <button type="button" 
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400" 
                        onclick="closeNewsPopup()">
                    Cancel
                </button>

                <!-- Bouton pour valider la suppression -->
                <button type="submit" 
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </div>
        </form>

    </div>
</div>

            

<script src="/js/news.js"></script>

<?php
    include '../includes/footer.php';
?>

