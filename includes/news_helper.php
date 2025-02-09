<?php

    // Inclusion de la configuration
    require_once '../config/config.php';
    // Déclaration explicite de $pdo en global pour accéder à la connexion à la base de données
    global $pdo;

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérification de la connexion à la base de données
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=news');
        exit;
    }

    // Récupération de tous les logiciels depuis la base de données
    // Utilisé pour l'ajout d'une news (possibilite de renseigner un software)
    try {
        $query = $pdo->query("SELECT * FROM software");
        $softwareItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching software data.';
        header('Location: /?page=news');
        exit;
    }

    // Récupération des news approuvées avec le nombre de commentaires associés
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

    // Récupération de tous les commentaires
    try {
        $commentQuery = $pdo->query("SELECT * FROM news_comment");
        $comments = $commentQuery->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching comments.';
        header('Location: /?page=news');
        exit;
    }

    // Gestion de la pagination
    $itemsPerPage = 5; // Nombre de news affichées par page
    $pageNumber = isset($_GET['page_number']) ? max(1, intval($_GET['page_number'])) : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    try {
        // Requête pour récupérer les news approuvées avec pagination
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

        // Récupération du nombre total de news approuvées pour gérer la pagination
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




    // Gestion des requêtes POST (ajout de commentaires, suppression, ajout de news)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Vérifie si un fichier trop volumineux a été envoyé
        if (empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $_SESSION['error_message'] = 'File is too large. Limit: ' . ini_get('upload_max_filesize');
            header('Location: /?page=news');
            exit;
        }



        if (isset($_POST['add_comment'])) {

            /**************************************/
            /*       Ajout d'un commentaire       */
            /**************************************/

            $news_id = $_POST['news_id'] ?? '';
            $comment = $_POST['comment'] ?? '';
            $profile_picture_path = '/uploads/account.png';
            $username = 'Anonymous';
            $user_id = null; // Par défaut, l'utilisateur n'est pas connecté
        
            // Vérifie si l'utilisateur est connecté et récupère ses informations
            if (isset($_SESSION['user']['id'])) {
                $user_id = $_SESSION['user']['id'];
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
        
            // Gestion de l'upload d'une photo de profil pour les visiteurs
            if (!isset($_SESSION['user']) && !empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $target_dir = __DIR__ . "/../public/uploads/";
                $file_tmp = $_FILES['profile_picture']['tmp_name'];
                $file_name = uniqid() . "_" . basename($_FILES['profile_picture']['name']);
                $target_file = $target_dir . $file_name;
        
                // Vérification du type de fichier
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
        
            // Insertion du commentaire en base de données
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
                    ':user_id' => $user_id
                ]);
        
                $_SESSION['success_message'] = 'Comment added successfully!';
                header('Location: /?page=news');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error adding comment.';
                header('Location: /?page=news');
                exit;
            }    



        } elseif (isset($_POST['delete_comment']) && isset($_POST['comment_id'])) {

            /**************************************/
            /*    Suppression d'un commentaire    */
            /**************************************/

            // Récupération et conversion en entier de l'ID du commentaire
            $comment_id = intval($_POST['comment_id']); 
    
            try {
                // Vérifier si le commentaire existe et récupérer l'ID de l'utilisateur qui l'a posté
                // Car les utilisateurs ont le droit de supprimer leurs propres commentaires
                $stmt = $pdo->prepare("SELECT userID FROM news_comment WHERE id = ?");
                $stmt->execute([$comment_id]);
                $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$comment) {
                    // Message d'erreur si le commentaire n'existe pas
                    $_SESSION['error_message'] = 'Comment not found.'; 
                    header('Location: /?page=news');
                    exit;
                }
    
                // Vérification des permissions : l'utilisateur doit être admin ou être l'auteur du commentaire
                if (hasPermission('admin') || (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $comment['userID'])) {
                    // Suppression du commentaire de la base de données
                    $stmt = $pdo->prepare("DELETE FROM news_comment WHERE id = ?");
                    $stmt->execute([$comment_id]);
    
                    $_SESSION['success_message'] = 'Comment successfully deleted.'; 
                    header('Location: /?page=news');
                    exit;
                } else {
                    // Erreur si l'utilisateur n'a pas les droits
                    $_SESSION['error_message'] = "You're not allowed to delete this comment."; 
                    header('Location: /?page=news');
                    exit;
                }
            } catch (PDOException $e) {
                // Gestion des erreurs SQL
                $_SESSION['error_message'] = 'Error deleting comment.'; 
                header('Location: /?page=news');
                exit;
            }



        } elseif (isset($_POST['delete_news']) && isset($_POST['news_id'])) {

            /**************************************/
            /*      Suppression d'une news        */
            /**************************************/
            
            // Récupération et conversion en entier de l'ID de la news
            $news_id = intval($_POST['news_id']); 
    
            // Vérification si l'utilisateur a le droit de supprimer des news (doit être admin)
            if (hasPermission('admin')) {
                try {
                    // Vérifier si la news existe et récupérer son URL d'image
                    $stmt = $pdo->prepare("SELECT id, image_url FROM news WHERE id = ?");
                    $stmt->execute([$news_id]);
                    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    
                    if ($news) {
                        // Suppression de l'image associée si elle existe et n'est pas par défaut
                        if (!empty($news['image_url']) && $news['image_url'] !== '/img/news/default.jpg') {
                            $imagePath = __DIR__ . '/../public' . $news['image_url'];
                            if (file_exists($imagePath)) {
                                unlink($imagePath); // Suppression du fichier image
                            }
                        }
    
                        // Suppression de la news de la base de données
                        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                        $stmt->execute([$news_id]);
    
                        $_SESSION['success_message'] = 'News successfully deleted.'; 
                    } else {
                        // Erreur si la news n'existe pas
                        $_SESSION['error_message'] = 'News not found.'; 
                    }
                } catch (PDOException $e) {
                    // Gestion des erreurs SQL
                    $_SESSION['error_message'] = 'Error deleting news.'; 
                }
            } else {
                // Erreur si l'utilisateur n'a pas les droits
                $_SESSION['error_message'] = "You're not allowed to delete this news."; 
            }
    
            // Redirection après traitement
            header('Location: /?page=news');
            exit;



        } elseif (isset($_POST['add_news'])) {

            /**************************************/
            /*         Ajout d'une news           */
            /**************************************/

            // Vérification si l'utilisateur a les droits pour ajouter une news (admin ou utilisateur)
            if (hasPermission('admin') || hasPermission('user')) {
                $title = $_POST['title'] ?? ''; // Titre de la news
                $content = $_POST['content'] ?? ''; // Contenu de la news
                $software = $_POST['software'] ?? ''; // Software associé à la news
                $published_date = $_POST['published_date'] ?? date('Y-m-d'); // Date de publication par défaut : aujourd'hui
                $image_path = '/img/news/default.jpg'; // Chemin par défaut de l'image
    
                // Gestion de l'upload d'une image pour la news
                if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $targetDir = __DIR__ . '/../public/img/news/'; // Répertoire de stockage des images
                    $fileTmp = $_FILES['image']['tmp_name']; // Fichier temporaire
                    $fileName = uniqid() . "_" . basename($_FILES['image']['name']); // Génération d'un nom unique pour l'image
                    $targetFile = $targetDir . $fileName;
    
                    // Vérification du type MIME pour s'assurer que le fichier est bien une image
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $fileType = mime_content_type($fileTmp);
    
                    if (!in_array($fileType, $allowedTypes)) {
                        // Erreur si le format est invalide
                        $_SESSION['error_message'] = 'Only JPG, PNG, and GIF files are allowed.'; 
                        header('Location: /?page=news');
                        exit;
                    } elseif (move_uploaded_file($fileTmp, $targetFile)) {
                        // Mise à jour du chemin de l'image si l'image est correcte 
                        $image_path = '/img/news/' . $fileName; 
                    } else {
                        // Erreur lors de l'upload
                        $_SESSION['error_message'] = 'Error during file upload.'; 
                        header('Location: /?page=news');
                        exit;
                    }
                }
    
                try {
                    // Insertion de la news en base de données avec statut "pending"
                    $stmt = $pdo->prepare("
                        INSERT INTO news (title, content, image_url, source, published_date, status, comments_count) 
                        VALUES (?, ?, ?, ?, ?, 'pending', 0)
                    ");
                    $stmt->execute([$title, $content, $image_path, $software, $published_date]);
    
                    $_SESSION['success_message'] = 'News submitted for approval!'; 
                    header('Location: /?page=news');
                    exit;
                } catch (PDOException $e) {
                    // Gestion des erreurs SQL
                    $_SESSION['error_message'] = 'Error adding news.'; 
                    header('Location: /?page=news');
                    exit;
                }
            } else {
                // Erreur si l'utilisateur n'a pas les droits
                $_SESSION['error_message'] = "You're not allowed to create a news."; 
                header('Location: /?page=news');
                exit;
            }
        }   
    }
