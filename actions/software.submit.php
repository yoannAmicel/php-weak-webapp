<?php 

    // Inclusion du fichier de configuration
    require_once '../config/config.php'; 

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'software.submit.php') {
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {

        /**************************************/
        /*       Ajout d'un software          */
        /**************************************/

        // Vérification du token CSRF pour éviter les attaques CSRF
        if (isset($_POST['csrf_token'])) {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
                http_response_code(403); 
                $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
                header('Location: /?page=software'); 
                exit;
            }
        } else {
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=software'); 
            exit;
        }

        // Vérifie si l'utilisateur a les permissions administrateur
        if (hasPermission('admin', $pdo)) {
            // Convertir le nom en majuscules
            $name = strtoupper(trim($_POST['name'] ?? ''));
            $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); // Protection XSS

            // Ajouter un "#" au début du nom si ce n'est pas déjà présent
            if (substr($name, 0, 1) !== '#') {
                $name = '#' . $name;
            }

            // Récupération des autres champs du formulaire
            $description = $_POST['description'] ?? '';
            $more_info_url = $_POST['more_info_url'] ?? '';
            $title_color = $_POST['title_color'] ?? '#000000'; // Valeur par défaut : noir

            // Nettoyer et vérifier la description
            $description = trim($_POST['description'] ?? '');
            $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

            // Vérifier et valider l'URL
            $more_info_url = trim($_POST['more_info_url'] ?? '');
            if (!filter_var($more_info_url, FILTER_VALIDATE_URL)) {
                $_SESSION['error_message'] = 'Invalid URL.';
                header('Location: /?page=software');
                exit;
            }

            // Vérification que tous les champs requis sont remplis
            if (!empty($name) && !empty($description) && !empty($more_info_url)) {
                try {
                    // Préparation et exécution de la requête d'insertion en base de données
                    $stmt = $pdo->prepare("
                        INSERT INTO software (name, description, more_info_url, title_color) 
                        VALUES (:name, :description, :more_info_url, :title_color)
                    ");
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                    $stmt->bindParam(':more_info_url', $more_info_url, PDO::PARAM_STR);
                    $stmt->bindParam(':title_color', $title_color, PDO::PARAM_STR);
                    $stmt->execute();

                    $_SESSION['success_message'] = 'Software successfully added!';
                    header('Location: /?page=software');
                    exit;
                } catch (PDOException $e) {
                    // Gestion d'erreur en cas de problème avec la base de données
                    error_log("Adding software error: " . $e->getMessage(), 3, '../logs/error.log');
                    $_SESSION['error_message'] = 'An error occurred while adding the software.';
                    header('Location: /?page=software');
                    exit;
                }
            } else {
                // Erreur si un champ requis est vide
                $_SESSION['error_message'] = 'Please fill out all fields.';
                header('Location: /?page=software');
                exit;
            }
        } else {
            // L'utilisateur n'a pas l'autorisation d'ajouter un software
            $_SESSION['error_message'] = "You're not allowed to add software.";
            header('Location: /?page=software');
            exit;
        }
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {

        /**************************************/
        /*     Suppression d'un software      */
        /**************************************/

        // Vérification du token CSRF pour éviter les attaques CSRF
        if (isset($_POST['csrf_token'])) {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
                http_response_code(403); 
                $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
                header('Location: /?page=software'); 
                exit;
            }
        } else {
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=software'); 
            exit;
        }

        // Vérifie si l'utilisateur a les permissions administrateur
        if (hasPermission('admin', $pdo)) {
            $id = $_POST['id'] ?? ''; // Récupération de l'ID du software à supprimer

            // Vérifier que l'ID est un entier avant suppression
            $id = $_POST['id'] ?? '';
            if (!ctype_digit($id)) {
                $_SESSION['error_message'] = 'Invalid ID.';
                header('Location: /?page=software');
                exit;
            }

            // Vérifie si l'ID est valide
            if (!empty($id)) {
                try {
                    // Préparation et exécution de la requête de suppression
                    $stmt = $pdo->prepare("DELETE FROM software WHERE id = :id");
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();

                    // Message de confirmation et redirection
                    $_SESSION['success_message'] = 'Software successfully deleted!';
                    header('Location: /?page=software');
                    exit;
                } catch (PDOException $e) {
                    // Gestion d'erreur en cas d'échec de la suppression
                    error_log("Deleting software error: " . $e->getMessage(), 3, '../logs/error.log');
                    $_SESSION['error_message'] = 'An error occurred while deleting the software.';
                    header('Location: /?page=software');
                    exit;
                }
            } else {
                // Erreur si l'ID n'est pas valide
                $_SESSION['error_message'] = 'Invalid ID for deletion.';
                header('Location: /?page=software');
                exit;
            }
        } else {
            // L'utilisateur n'a pas l'autorisation de supprimer un software
            $_SESSION['error_message'] = "You're not allowed to delete software.";
            header('Location: /?page=software');
            exit;
        }
    }
