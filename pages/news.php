<!DOCTYPE html>

<?php
    include '../includes/header.php';
    require_once '../config/config.php';
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=news');
        exit;
    }

    // Récupérer les éléments de la table software
    try {
        $query = $pdo->query("SELECT * FROM software");
        $softwareItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching software data.';
        header('Location: /?page=news');
        exit;
    }

    // Récupérer uniquement les news approuvées avec le nombre de commentaires
    try {
        $query = $pdo->query("
            SELECT n.*, COUNT(nc.id) AS comments_count
            FROM news n
            LEFT JOIN news_comment nc ON n.id = nc.news_id
            WHERE n.status = 'approved'
            GROUP BY n.id
        ");
        $newsItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching approved news.';
        header('Location: /?page=news');
        exit;
    }


    // Récupérer les éléments de la table news_comment
    try {
        $commentQuery = $pdo->query("SELECT * FROM news_comment");
        $comments = $commentQuery->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching comments.';
        header('Location: /?page=news');
        exit;
    }

    // Pagination logic
    $itemsPerPage = 5; // Nombre de news par page
    $pageNumber = isset($_GET['page_number']) ? max(1, intval($_GET['page_number'])) : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    try {
        $stmt = $pdo->prepare("
            SELECT n.*, COUNT(nc.id) AS comments_count
            FROM news n
            LEFT JOIN news_comment nc ON n.id = nc.news_id
            WHERE n.status = 'approved'
            GROUP BY n.id
            ORDER BY n.published_date DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer le nombre total de news approuvées pour la pagination
        $countStmt = $pdo->query("
            SELECT COUNT(*) 
            FROM news 
            WHERE status = 'approved'
        ");
        $totalNews = $countStmt->fetchColumn();
        $totalPages = ceil($totalNews / $itemsPerPage);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching approved news.';
        $newsItems = [];
        $totalPages = 1;
    }




    // Si une requête de type POST est envoyée
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // On ne peut traiter les fichiers plus lourds que la configuration le permet
        if (empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $_SESSION['error_message'] = 'File is too large. Limit: ' . ini_get('upload_max_filesize');
            header('Location: /?page=news');
            exit;
        }

        // En cas d'ajout d'un commentaire
        if (isset($_POST['add_comment'])) {
            $news_id = $_POST['news_id'] ?? '';
            $comment = $_POST['comment'] ?? '';
            $profile_picture_path = '/uploads/account.png';
            $username = 'Anonymous';
            $user_id = null; // Par défaut, l'utilisateur n'est pas connecté
        
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
                    $_SESSION['error_message'] = 'Error fetching user data.';
                    header('Location: /?page=news');
                    exit;
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
                    $_SESSION['error_message'] = 'Only JPG, PNG, and GIF files are allowed.';
                    header('Location: /?page=news');
                    exit;
                } elseif (move_uploaded_file($file_tmp, $target_file)) {
                    $profile_picture_path = '/uploads/' . $file_name;
                } else {
                    $_SESSION['error_message'] = 'Error during file upload.';
                    header('Location: /?page=news');
                    exit;
                }
            }
        
            // Insertion en base de données avec le chemin de l'image et l'ID utilisateur (s'il est connecté)
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO news_comment (news_id, username, comment, profile_picture, userID, created_at) 
                    VALUES (:news_id, :username, :comment, :profile_picture, :user_id, NOW())
                ");
                $stmt->execute([
                    ':news_id' => $news_id,
                    ':username' => $username,
                    ':comment' => $comment,
                    ':profile_picture' => $profile_picture_path,
                    ':user_id' => $user_id // Null si l'utilisateur n'est pas connecté
                ]);
        
                $_SESSION['flash_message'] = 'Comment added successfully!';
                header('Location: /?page=news');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error adding comment.';
                header('Location: /?page=news');
                exit;
            }    

        } elseif (isset($_POST['delete_comment']) && isset($_POST['comment_id'])) {
            // En cas de suppression d'un commentaire
            $comment_id = intval($_POST['comment_id']);

            try {
                // Récupérer les informations du commentaire
                $stmt = $pdo->prepare("SELECT userID FROM news_comment WHERE id = ?");
                $stmt->execute([$comment_id]);
                $comment = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$comment) {
                    $_SESSION['error_message'] = 'Comment not found.';
                    header('Location: /?page=news');
                    exit;
                }

                // Vérifier les permissions
                if (hasPermission('admin') || (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $comment['userID'])) {
                    // Supprimer le commentaire
                    $stmt = $pdo->prepare("DELETE FROM news_comment WHERE id = ?");
                    $stmt->execute([$comment_id]);

                    $_SESSION['flash_message'] = 'Comment successfully deleted.';
                    header('Location: /?page=news');
                    exit;
                } else {
                    $_SESSION['error_message'] = "You're not allowed to delete this comment.";
                    header('Location: /?page=news');
                    exit;
                }
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error deleting comment.';
                header('Location: /?page=news');
                exit;
            }

        } elseif (isset($_POST['delete_news']) && isset($_POST['news_id'])) {
            // En cas de suppression d'une news
            $news_id = intval($_POST['news_id']);
            
            // Vérification si l'utilisateur a les permissions nécessaires
            if (hasPermission('admin')) {
                try {
                    // Vérification si la news existe
                    $stmt = $pdo->prepare("SELECT id, image_url FROM news WHERE id = ?");
                    $stmt->execute([$news_id]);
                    $news = $stmt->fetch(PDO::FETCH_ASSOC);
        
                    if ($news) {
                        // Supprimer l'image associée si elle existe et n'est pas par défaut
                        if (!empty($news['image_url']) && $news['image_url'] !== '/img/news/default.jpg') {
                            $imagePath = __DIR__ . '/../public' . $news['image_url'];
                            if (file_exists($imagePath)) {
                                unlink($imagePath); // Supprime l'image du serveur
                            }
                        }
        
                        // Suppression de la news
                        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                        $stmt->execute([$news_id]);
        
                        $_SESSION['flash_message'] = 'News successfully deleted.';
                    } else {
                        $_SESSION['error_message'] = 'News not found.';
                    }
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'Error deleting news: ' . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = "You're not allowed to delete this news.";
            }
        
            // Redirection après traitement
            header('Location: /?page=news');
            exit;
            
            
        } elseif (isset($_POST['add_news'])) {
            // En cas d'ajout d'une news
            if (hasPermission('admin') || hasPermission('user')) {
                $title = $_POST['title'] ?? '';
                $content = $_POST['content'] ?? '';
                $software = $_POST['software'] ?? '';
                $published_date = $_POST['published_date'] ?? date('Y-m-d');
                $image_path = '/img/news/default.jpg'; // Chemin par défaut
        
                // Gestion de l'upload d'image
                if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $targetDir = __DIR__ . '/../public/img/news/';
                    $fileTmp = $_FILES['image']['tmp_name'];
                    $fileName = uniqid() . "_" . basename($_FILES['image']['name']);
                    $targetFile = $targetDir . $fileName;
        
                    // Vérification du type MIME
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = mime_content_type($fileTmp);
        
                    if (!in_array($fileType, $allowedTypes)) {
                        $_SESSION['error_message'] = 'Only JPG, PNG, and GIF files are allowed.';
                        header('Location: /?page=news');
                        exit;
                    } elseif (move_uploaded_file($fileTmp, $targetFile)) {
                        $image_path = '/img/news/' . $fileName;
                    } else {
                        $_SESSION['error_message'] = 'Error during file upload.';
                        header('Location: /?page=news');
                        exit;
                    }
                }
        
                try {
                    // Insertion de la news avec statut "pending"
                    $stmt = $pdo->prepare("INSERT INTO news (title, content, image_url, source, published_date, status, comments_count) VALUES (?, ?, ?, ?, ?, 'pending', 0)");
                    $stmt->execute([$title, $content, $image_path, $software, $published_date]);
        
                    $_SESSION['flash_message'] = 'News submitted for approval!';
                    header('Location: /?page=news');
                    exit;
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'Error adding news.';
                    header('Location: /?page=news');
                    exit;
                }
            } else {
                $_SESSION['error_message'] = "You're not allowed to create a news.";
                header('Location: /?page=news');
                exit;
            }
        }    
    }
?>


<head>
    <title>News</title>
</head>


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

        <?php if (isset($_SESSION['user']) && (hasPermission('user') || hasPermission('admin'))): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden relative p-6 w-full mx-auto mb-8">
                <h2 class="text-2xl font-bold mb-4">Add News</h2>
                <form method="POST" enctype="multipart/form-data">
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
                <div class="bg-white rounded-lg shadow-md overflow-hidden relative">
                    <img src="<?= htmlspecialchars($news['image_url'] ?? 'img/default.jpg') ?>" 
                        alt="<?= htmlspecialchars($news['title'] ?? 'No Title') ?>" 
                        class="w-full h-64 object-cover">
                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold mb-2">
                                <?= htmlspecialchars($news['title'] ?? 'No Title') ?>
                            </h2>
                            <?php if (isset($_SESSION['user']) && hasPermission('admin')): ?>
                                <button type="button" 
                                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"
                                        onclick="openNewsPopup(<?= htmlspecialchars($news['id']) ?>, '<?= htmlspecialchars($news['title']) ?>')">
                                    Delete
                                </button>
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
                                <?= htmlspecialchars($news['comments_count']) ?> Comments
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
                                            <?php if (isset($_SESSION['user']) && (hasPermission('admin') || $_SESSION['user']['id'] == $comment['userID'])): ?>
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
        <!-- Pagination -->
<div class="mt-4 flex justify-center space-x-2">
    <?php if ($pageNumber > 1): ?>
        <a href="?page=news&page_number=<?= $pageNumber - 1 ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
            Previous
        </a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=news&page_number=<?= $i ?>" 
           class="px-4 py-2 rounded <?= $i === $pageNumber ? 'bg-blue-700 text-white' : 'bg-gray-200 text-blue-500 hover:bg-gray-300' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($pageNumber < $totalPages): ?>
        <a href="?page=news&page_number=<?= $pageNumber + 1 ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
            Next
        </a>
    <?php endif; ?>
</div>

    </div>

<!-- Popup -->
<div id="delete-news-popup" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-1/3">
        <h3 class="text-xl font-bold mb-4 text-red-600">Confirm Deletion</h3>
        <p id="popup-news-message" class="mb-6 text-gray-700"></p>
        <form id="delete-news-form" method="POST" action="">
            <input type="hidden" id="delete-news-id" name="news_id" value="">
            <input type="hidden" name="delete_news" value="1">
            <div class="flex justify-end space-x-4">
                <button type="button" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400" onclick="closeNewsPopup()">Cancel</button>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Delete</button>
            </div>
        </form>
    </div>
</div>


<script src="/js/news.js"></script>

<?php
    include '../includes/footer.php';
?>

