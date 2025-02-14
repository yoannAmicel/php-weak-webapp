<?php

    include_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'validation.submit.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }   

    // Vérifie que la connexion à la base de données est bien initialisée
    if (!isset($pdo)) {
        // Renvoie un message d'erreur si la variable n'est pas initialisée
        $_SESSION['error_message'] = "Error: Database connection not defined.";
        header('Location: /?page=home');
        exit;
    }

    // Vérification que l'utilisateur soit bien administrateur
    if (!isset($_SESSION['user']) || !hasPermission('admin', $pdo)) {
        // Redirection sur la page d'accueil s'il n'est pas admin
        $_SESSION['error_message'] = "You're not allowed to access this page.";
        header('Location: /?page=login'); 
        exit;
    }


    // Gestion des actions (approbation & rejet de news)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {    

        // Vérification du token CSRF pour éviter les attaques CSRF
        if (isset($_POST['csrf_token'])) {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
                http_response_code(403); 
                $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
                header('Location: /?page=validation'); 
                exit;
            }
        } else {
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=validation'); 
            exit;
        }

        $news_id = intval($_POST['news_id'] ?? 0); // Récupération et conversion sécurisée de l'ID de la news
        $news_id = filter_input(INPUT_POST, 'news_id', FILTER_VALIDATE_INT);
        if (!$news_id) {
            $_SESSION['error_message'] = "Invalid news ID.";
            header('Location: /?page=validation');
            exit;
        }

        if (isset($_POST['approve'])) {

            /**************************************/
            /*           Approbation              */
            /**************************************/

            try {
                $stmt = $pdo->prepare("UPDATE news SET status = 'approved' WHERE id = ?");
                $stmt->execute([$news_id]); // Mise à jour du statut de la news à "approved"
                $_SESSION['success_message'] = 'News approved successfully.';

            } catch (PDOException $e) {
                error_log("Approving news error: " . $e->getMessage(), 3, '../logs/error.log');
                $_SESSION['error_message'] = 'Error approving news.';
            }
            header('Location: /?page=validation');
            exit;
        } 
        

        elseif (isset($_POST['reject'])) {

            /**************************************/
            /*             Rejet                  */
            /**************************************/

            try {
                // Récupération des informations de la news (y compris l'image associée)
                $stmt = $pdo->prepare("SELECT image_url FROM news WHERE id = ?");
                $stmt->execute([$news_id]);
                $news = $stmt->fetch(PDO::FETCH_ASSOC);

                // Vérifie si la news possède une image et qu'elle n'est pas l'image par défaut
                if ($news && !empty($news['image_url']) && $news['image_url'] !== '/img/news/default.jpg') {
                    $imagePath = realpath(__DIR__ . '/../public' . $news['image_url']);
                    $allowedPath = realpath(__DIR__ . '/../public/img/news/');
                    // Vérifie que l’image est bien dans le dossier autorisé
                    if ($imagePath !== false && strpos($imagePath, $allowedPath) === 0) {
                        unlink($imagePath);
                    }
                }

                // Supprime la news de la base de données après suppression de l'image
                $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$news_id]);

                $_SESSION['success_message'] = 'News rejected and deleted successfully.';
            } catch (PDOException $e) {
                error_log("Rejecting news error: " . $e->getMessage(), 3, '../logs/error.log');
                $_SESSION['error_message'] = 'Error rejecting news.';
            }
            header('Location: /?page=validation');
            exit;
        }
    }

?>
