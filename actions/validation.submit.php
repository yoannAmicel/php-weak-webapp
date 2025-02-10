<?php

    include_once '../config/config.php';

    // Vérification que l'utilisateur soit bien administrateur
    if (!isset($_SESSION['user']) || !hasPermission('admin', $pdo)) {
        // Redirection sur la page d'accueil s'il n'est pas admin
        $_SESSION['error_message'] = "You're not allowed to access this page.";
        header('Location: /?page=login'); 
        exit;
    }


    // Gestion des actions (approbation & rejet de news)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {    

        $news_id = intval($_POST['news_id'] ?? 0); // Récupération et conversion sécurisée de l'ID de la news


        if (isset($_POST['approve'])) {

            /**************************************/
            /*           Approbation              */
            /**************************************/

            try {
                $stmt = $pdo->prepare("UPDATE news SET status = 'approved' WHERE id = ?");
                $stmt->execute([$news_id]); // Mise à jour du statut de la news à "approved"
                $_SESSION['success_message'] = 'News approved successfully.';

            } catch (PDOException $e) {
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
                    $imagePath = __DIR__ . '/../public' . $news['image_url'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath); // Supprime le fichier image du serveur
                    }
                }

                // Supprime la news de la base de données après suppression de l'image
                $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                $stmt->execute([$news_id]);

                $_SESSION['success_message'] = 'News rejected and deleted successfully.';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error rejecting news.';
            }
            header('Location: /?page=validation');
            exit;
        }
    }

?>
