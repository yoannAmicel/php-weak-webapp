<!DOCTYPE html>


<head>
    <title>News</title>
</head>

<?php
    include '../includes/header.php';
    require_once '../config/config.php';
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($pdo)) {
        die('Erreur : connexion à la base de données non définie.');
    }

    // Récupérer les éléments de la table software
    try {
        $query = $pdo->query("SELECT * FROM software");
        $softwareItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des logiciels : ' . $e->getMessage());
    }

    // Récupérer les éléments de la table news
    try {
        $query = $pdo->query("SELECT * FROM news");
        $newsItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des données : ' . $e->getMessage());
    }

    // Récupérer les éléments de la table news_comment
    try {
        $commentQuery = $pdo->query("SELECT * FROM news_comment");
        $comments = $commentQuery->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erreur lors de la récupération des commentaires : ' . $e->getMessage());
    }

    // Si une requete de type POST est envoyee
    if($_SERVER['REQUEST_METHOD'] === 'POST') {

        // On ne peut traiter les fichiers plus lourds que la configuration le permet
        if(empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $_SESSION['error_message'] = 'Fichier trop volumineux. Limite : ' . ini_get('upload_max_filesize');
            header('Location: /?page=news');
            exit;
        }

        // En cas d'ajout d'un commentaire
        if (isset($_POST['add_comment'])) {
            $news_id = $_POST['news_id'] ?? '';
            $comment = $_POST['comment'] ?? '';
            $profile_picture_path = '/uploads/account.png'; 
            $username = 'Anonymous';
        
            // Vérification si l'utilisateur est connecté
            if (isset($_SESSION['user']['id'])) {
                $user_id = $_SESSION['user']['id'];
        
                // Récupération des données utilisateur depuis la base de données
                try {
                    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
                    if ($user) {
                        $username = $user['name'];
                        $profile_picture_path = $user['profile_picture'];
                    }
                } catch (PDOException $e) {
                    die('Erreur lors de la récupération des données utilisateur : ' . $e->getMessage());
                }
            } elseif (!empty($_POST['name'])) {
                $username = $_POST['name'];
            }
        
            // Dossier d'uploads pour les visiteurs
            $target_dir = __DIR__ . "/../public/uploads/";
        
            // Gestion de l'upload de la photo de profil pour les visiteurs
            if (!isset($_SESSION['user']) && !empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['profile_picture']['tmp_name'];
                $file_name = uniqid() . "_" . basename($_FILES['profile_picture']['name']);
                $target_file = $target_dir . $file_name;
        
                // Vérification du type MIME
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($file_tmp);
        
                if (!in_array($file_type, $allowed_types)) {
                    $_SESSION['error_message'] = 'Seuls les fichiers JPG, PNG et GIF sont autorisés.';
                    header('Location: /?page=news');
                    exit;
                } elseif (move_uploaded_file($file_tmp, $target_file)) {
                    $profile_picture_path = '/uploads/' . $file_name; // Stocke le chemin pour la base de données
                } else {
                    $_SESSION['error_message'] = 'Erreur lors de l’upload du fichier.';
                    header('Location: /?page=news');
                    exit;
                }
            }
        
            // Insertion en base de données avec le chemin de l'image
            try {
                $stmt = $pdo->prepare("INSERT INTO news_comment (news_id, username, comment, profile_picture, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$news_id, $username, $comment, $profile_picture_path]);
        
                $_SESSION['flash_message'] = 'Commentaire ajouté avec succès!';
                header('Location: /?page=news');
                exit;
            } catch (PDOException $e) {
                die('Erreur lors de l’ajout du commentaire : ' . $e->getMessage());
            }
        }
        


            // En cas de suppression d'un commentaire
         else if (isset($_POST['delete_comment']) && isset($_POST['comment_id'])) {
            $comment_id = intval($_POST['comment_id']); // Sécuriser l'ID du commentaire
        
            try {
                // Supprimer le commentaire de la base de données
                $stmt = $pdo->prepare("DELETE FROM news_comment WHERE id = ?");
                $stmt->execute([$comment_id]);
        
                $_SESSION['flash_message'] = 'Commentaire supprimé avec succès.';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erreur lors de la suppression du commentaire : ' . $e->getMessage();
            }
        
            // Redirection pour éviter le rechargement du formulaire
            header('Location: /?page=news');
            exit;
        

            // En cas de suppression d'une news
        } else if (isset($_POST['delete_news']) && isset($_POST['news_id'])) {
            $news_id = intval($_POST['news_id']); 
        
            try {
                // Supprimer la news de la base de données
                $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$news_id]);
        
                $_SESSION['flash_message'] = 'News supprimée avec succès.';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erreur lors de la suppression de la news : ' . $e->getMessage();
            }
        
            // Redirection pour éviter le rechargement du formulaire
            header('Location: /?page=news');
            exit;
        
            // En cas d'ajout d'une news
        } else if (isset($_POST['add_news'])) {
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $image_url = $_POST['image_url'] ?? '';
            $software = $_POST['software'] ?? '';
            $published_date = $_POST['published_date'] ?? date('Y-m-d');
        
            try {
                $stmt = $pdo->prepare("INSERT INTO news (title, content, image_url, source, published_date, comments_count) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt->execute([$title, $content, $image_url, $software, $published_date]);
        
                $_SESSION['flash_message'] = 'News added successfully!';
                header('Location: /?page=news');
                exit;
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = 'Error adding news: ' . $e->getMessage();
            }
        }        
    } 
?>


<div class="container mx-auto px-4 py-8 max-w-5xl">
    <h1 class="text-3xl font-bold text-center mb-8">NEWS</h1>

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

    <?php if (isset($_SESSION['user'])): ?>
        <div class="bg-white p-6 rounded-lg shadow-md mb-8 w-2/3 mx-auto">
            <h2 class="text-2xl font-bold mb-4">Add New News</h2>
            <form method="POST">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 font-bold mb-2">Title:</label>
                    <input type="text" id="title" name="title" class="border rounded w-full py-2 px-3" required>
                </div>
                <div class="mb-4">
                    <label for="content" class="block text-gray-700 font-bold mb-2">Content:</label>
                    <textarea id="content" name="content" class="border rounded w-full py-2 px-3" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="image_url" class="block text-gray-700 font-bold mb-2">Image URL:</label>
                    <input type="text" id="image_url" name="image_url" class="border rounded w-full py-2 px-3">
                </div>
                <div class="mb-4">
                    <label for="software" class="block text-gray-700 font-bold mb-2">Software:</label>
                    <select id="software" name="software" class="border rounded w-full py-2 px-3">
                        <?php foreach ($softwareItems as $software): ?>
                            <option value="<?= htmlspecialchars(ltrim($software['name'], '#')) ?>" style="background-color: <?= htmlspecialchars($software['title_color']) ?>; color: #ffffff;">
                                <?= htmlspecialchars(ltrim($software['name'], '#')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="published_date" class="block text-gray-700 font-bold mb-2">Published Date:</label>
                    <input type="date" id="published_date" name="published_date" class="border rounded w-full py-2 px-3" value="<?= date('Y-m-d') ?>" required>
                </div>
                <button type="submit" name="add_news" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add</button>
            </form>
        </div>
    <?php endif; ?>



    <div class="grid gap-12">
        <?php foreach ($newsItems as $news): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden relative">
                <img src="<?= htmlspecialchars($news['image_url'] ?? 'img/default.jpg') ?>" 
                     alt="<?= htmlspecialchars($news['title'] ?? 'No Title') ?>" 
                     class="w-full h-64 object-cover">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold mb-2">
                            <?= htmlspecialchars($news['title'] ?? 'No Title') ?>
                        </h2>
                        <?php if (isset($_SESSION['user'])): ?>
                            <form method="POST" action="" class="ml-4">
                                <input type="hidden" name="delete_news" value="1">
                                <input type="hidden" name="news_id" value="<?= htmlspecialchars($news['id']) ?>">
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
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
                    <p class="text-gray-700 mb-4 truncate">
                        <?= htmlspecialchars($news['content'] ?? 'No Content Available') ?>
                    </p>
                    <?php if (strlen($news['content']) > 100 || $news['comments_count'] > 0): ?>
                        <button onclick="toggleContent(this)" class="text-indigo-600 hover:text-indigo-800 font-bold">More info →</button>
                    <?php endif; ?>

                    <?php if ($news['comments_count'] > 0): ?>
                        <div class="comments-section">
                            <h3 class="text-lg font-semibold mb-2">Comments:</h3>
                            <?php foreach ($comments as $comment): ?>
                                <?php if ($comment['news_id'] == $news['id']): ?>
                                    <div class="flex items-start mb-4">
                                        <!-- Image et contenu du commentaire -->
                                        <img src="<?= htmlspecialchars($comment['profile_picture'] ?? '/uploads/account.png') ?>" alt="Profile Picture" class="w-12 h-12 rounded-full mr-4">
                                        <div class="flex-grow">
                                            <p class="font-bold text-gray-800">
                                                <?= htmlspecialchars($comment['username'] ?? 'Anonymous') ?>
                                            </p>
                                            <p class="text-gray-600">"<?= htmlspecialchars($comment['comment'] ?? '') ?>"</p>
                                            <p class="text-xs text-gray-400">Posted on <?= htmlspecialchars($comment['created_at'] ?? '') ?></p>
                                        </div>
                                        <!-- Bouton Delete visible uniquement si l'utilisateur est connecté -->
                                        <?php if (isset($_SESSION['user'])): ?>
                                            <form method="POST" action="" class="ml-4">
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
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="news_id" value="<?= htmlspecialchars($news['id']) ?>">
                            <!-- Champ photo de profil -->
                            <?php if (!isset($_SESSION['user'])): ?>
                                <div class="mb-4">
                                    <label for="profile_picture_<?= htmlspecialchars($news['id']) ?>" class="block text-gray-700 font-bold mb-1">Upload Profile Picture:</label>
                                    <input type="file" id="profile_picture_<?= htmlspecialchars($news['id']) ?>" name="profile_picture" class="border rounded w-full py-2 px-3">
                                </div>
                            <?php endif; ?>

                            <!-- Champ nom -->
                            <?php if (!isset($_SESSION['user'])): ?>
                                <div class="mb-4">
                                    <label for="name_<?= htmlspecialchars($news['id']) ?>" class="block text-gray-700 font-bold mb-1">Name:</label>
                                    <input type="text" id="name_<?= htmlspecialchars($news['id']) ?>" name="name" class="border rounded w-full py-2 px-3">
                                </div>
                            <?php endif; ?>

                            <!-- Champ commentaire -->
                            <div class="mb-4">
                                <label for="comment_<?= htmlspecialchars($news['id']) ?>" class="block text-gray-700 font-bold mb-1">Comment:</label>
                                <textarea id="comment_<?= htmlspecialchars($news['id']) ?>" name="comment" class="border rounded w-full py-2 px-3" required></textarea>
                            </div>
                            <button type="submit" name="add_comment" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
    include '../includes/footer.php';
?>

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