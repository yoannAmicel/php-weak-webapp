<?php

    // Inclusion de la configuration
    require_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'news.submit.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // Vérification de la connexion à la base de données
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=news');
        exit;
    }


    // Gestion des requêtes POST (ajout de commentaires, suppression, ajout de news)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // S.News.2 - Vérification du token CSRF pour éviter les attaques CSRF
        if (isset($_POST['csrf_token'])) {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
                http_response_code(403); 
                $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
                header('Location: /?page=news'); 
                exit;
            }
        } else {
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=news'); 
            exit;
        }

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

            // S.News.1 - Filtrage SQLi
            // Vérification et filtrage de l'ID de la news (doit être un entier valide)
            if (!isset($_POST['news_id']) || !filter_var($_POST['news_id'], FILTER_VALIDATE_INT)) {
                $_SESSION['error_message'] = "Invalid News ID.";
                header('Location: /?page=news');
                exit;
            }
            $news_id = intval($_POST['news_id']);

            // Vérification de l'existence de la news avant d'insérer un commentaire
            $stmt = $pdo->prepare("SELECT id FROM news WHERE id = ?");
            $stmt->execute([$news_id]);
            if (!$stmt->fetchColumn()) {
                $_SESSION['error_message'] = "News not found.";
                header('Location: /?page=news');
                exit;
            }

            // Validation du commentaire (évite les scripts malveillants)
            $comment = trim($_POST['comment'] ?? '');
            if (empty($comment) || strlen($comment) > 500) {
                $_SESSION['error_message'] = "Invalid comment. It must be between 1 and 500 characters.";
                header('Location: /?page=news');
                exit;
            }
            $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

            // Vérification et sécurisation du nom d'utilisateur
            $username = isset($_POST['name']) ? trim($_POST['name']) : 'Anonymous';
            if (!empty($username) && strlen($username) > 50) {
                $_SESSION['error_message'] = "Invalid username. Max 50 characters.";
                header('Location: /?page=news');
                exit;
            }
            $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');


        
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
                    error_log("Retrieving user data error: " . $e->getMessage(), 3, '../logs/error.log');
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
        
            
            try {
                // S.News.5 - Limitation des tentatives d’ajout d'un commentaire
                $ipAddress = $_SERVER['REMOTE_ADDR'];

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_attempts WHERE ip_address = ? AND created_at >= NOW() - INTERVAL 5 MINUTE");
                $stmt->execute([$ipAddress]);
                $attemptCount = $stmt->fetchColumn();
                
                // Blocage après 3 ajouts en moins de 5 minutes
                if ($attemptCount >= 3) {
                    $_SESSION['error_message'] = 'Too many comments. Please wait.';
                    header('Location: /?page=news');
                    exit;
                }              
                
                // Enregistrer la tentative dans la base de données
                $stmt = $pdo->prepare("INSERT INTO comment_attempts (ip_address, created_at) VALUES (?, NOW())");
                $stmt->execute([$ipAddress]); 

                // Insertion du commentaire en base de données
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
                error_log("Adding comment error: " . $e->getMessage(), 3, '../logs/error.log');
                $_SESSION['error_message'] = 'Error adding comment.';
                header('Location: /?page=news');
                exit;
            }    



        } elseif (isset($_POST['delete_comment']) && isset($_POST['comment_id'])) {

            /**************************************/
            /*    Suppression d'un commentaire    */
            /**************************************/

            // S.News.1 - Filtrage SQLi
            // Récupération et conversion en entier de l'ID du commentaire
            if (!isset($_POST['comment_id']) || !filter_var($_POST['comment_id'], FILTER_VALIDATE_INT)) {
                $_SESSION['error_message'] = "Invalid Comment ID.";
                header('Location: /?page=news');
                exit;
            }
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
                if (hasPermission('admin', $pdo) || (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $comment['userID'])) {
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
                error_log("Deleting comment error: " . $e->getMessage(), 3, '../logs/error.log');
                $_SESSION['error_message'] = 'Error deleting comment.'; 
                header('Location: /?page=news');
                exit;
            }



        } elseif (isset($_POST['delete_news']) && isset($_POST['news_id'])) {

            /**************************************/
            /*      Suppression d'une news        */
            /**************************************/
            
            // S.News.1 - Filtrage SQLi
            // Vérification et filtrage de l'ID de la news (doit être un entier valide)
            if (!isset($_POST['news_id']) || !filter_var($_POST['news_id'], FILTER_VALIDATE_INT)) {
                $_SESSION['error_message'] = "Invalid News ID.";
                header('Location: /?page=news');
                exit;
            }
            $news_id = intval($_POST['news_id']);
    
            // Vérification si l'utilisateur a le droit de supprimer des news (doit être admin)
            if (hasPermission('admin', $pdo)) {
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
                    error_log("Deleting news error: " . $e->getMessage(), 3, '../logs/error.log');
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
            if (hasPermission('admin', $pdo) || hasPermission('user', $pdo)) {
                $title = $_POST['title'] ?? ''; // Titre de la news
                $content = $_POST['content'] ?? ''; // Contenu de la news
                $software = $_POST['software'] ?? ''; // Software associé à la news
                $published_date = $_POST['published_date'] ?? date('Y-m-d'); // Date de publication par défaut : aujourd'hui
                $image_path = '/img/news/default.jpg'; // Chemin par défaut de l'image

                // Vérification et filtrage du titre de la news
                $title = trim($_POST['title'] ?? '');
                if (empty($title) || strlen($title) > 255) {
                    $_SESSION['error_message'] = "Invalid title. Max 255 characters.";
                    header('Location: /?page=news');
                    exit;
                }
                $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

                // Vérification et filtrage du contenu
                $content = trim($_POST['content'] ?? '');
                if (empty($content) || strlen($content) > 5000) {
                    $_SESSION['error_message'] = "Invalid content. Max 5000 characters.";
                    header('Location: /?page=news');
                    exit;
                }
                $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

                // Vérification et validation du logiciel associé
                $software = trim($_POST['software'] ?? '');
                if (!empty($software) && strlen($software) > 100) {
                    $_SESSION['error_message'] = "Invalid software name.";
                    header('Location: /?page=news');
                    exit;
                }
                $software = htmlspecialchars($software, ENT_QUOTES, 'UTF-8');

                // Vérification de la date de publication
                $published_date = $_POST['published_date'] ?? date('Y-m-d');
                if (!DateTime::createFromFormat('Y-m-d', $published_date)) {
                    $_SESSION['error_message'] = "Invalid date format.";
                    header('Location: /?page=news');
                    exit;
                }

    
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
                    
                    // S.News.5 - Limitation des tentatives d’ajout de news
                    $ipAddress = $_SERVER['REMOTE_ADDR'];

                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_attempts WHERE ip_address = ? AND created_at >= NOW() - INTERVAL 5 MINUTE");
                    $stmt->execute([$ipAddress]);
                    $attemptCount = $stmt->fetchColumn();
                    
                    // Blocage après 3 ajouts en moins de 5 minutes
                    if ($attemptCount >= 3) {
                        $_SESSION['error_message'] = 'Too many news. Please wait.';
                        header('Location: /?page=news');
                        exit;
                    }              
                    
                    // Enregistrer la tentative dans la base de données
                    $stmt = $pdo->prepare("INSERT INTO comment_attempts (ip_address, created_at) VALUES (?, NOW())");
                    $stmt->execute([$ipAddress]);
                    
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
                    error_log("Adding news error: " . $e->getMessage(), 3, '../logs/error.log');
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