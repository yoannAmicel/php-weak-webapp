<?php 

    // Inclusion du fichier de configuration
    require_once '../config/config.php'; 
    // Déclaration explicite de $pdo en global pour accéder à la connexion à la base de données
    global $pdo;

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si la connexion PDO est bien définie
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=error');
        exit;
    }

    // Récupération de la liste des softwares
    try {
        $query = $pdo->query("SELECT * FROM software");
        $softwareItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Gestion d'erreur si la récupération des données échoue
        $_SESSION['error_message'] = 'An error occurred while fetching the data.';
        header('Location: /?page=software');
        exit;
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {

        /**************************************/
        /*       Ajout d'un software          */
        /**************************************/

        // Vérifie si l'utilisateur a les permissions administrateur
        if (hasPermission('admin')) {
            // Convertir le nom en majuscules
            $name = strtoupper($_POST['name'] ?? ''); 

            // Ajouter un "#" au début du nom si ce n'est pas déjà présent
            if (substr($name, 0, 1) !== '#') {
                $name = '#' . $name;
            }

            // Récupération des autres champs du formulaire
            $description = $_POST['description'] ?? '';
            $more_info_url = $_POST['more_info_url'] ?? '';
            $title_color = $_POST['title_color'] ?? '#000000'; // Valeur par défaut : noir

            // Vérification que tous les champs requis sont remplis
            if (!empty($name) && !empty($description) && !empty($more_info_url)) {
                try {
                    // Préparation et exécution de la requête d'insertion en base de données
                    $stmt = $pdo->prepare("
                        INSERT INTO software (name, description, more_info_url, title_color) 
                        VALUES (:name, :description, :more_info_url, :title_color)
                    ");
                    $stmt->execute([
                        ':name' => $name,
                        ':description' => $description,
                        ':more_info_url' => $more_info_url,
                        ':title_color' => $title_color
                    ]);

                    $_SESSION['success_message'] = 'Software successfully added!';
                } catch (PDOException $e) {
                    // Gestion d'erreur en cas de problème avec la base de données
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

        // Vérifie si l'utilisateur a les permissions administrateur
        if (hasPermission('admin')) {
            $id = $_POST['id'] ?? ''; // Récupération de l'ID du software à supprimer

            // Vérifie si l'ID est valide
            if (!empty($id)) {
                try {
                    // Préparation et exécution de la requête de suppression
                    $stmt = $pdo->prepare("DELETE FROM software WHERE id = :id");
                    $stmt->execute([':id' => $id]);

                    // Message de confirmation et redirection
                    $_SESSION['success_message'] = 'Software successfully deleted!';
                } catch (PDOException $e) {
                    // Gestion d'erreur en cas d'échec de la suppression
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
