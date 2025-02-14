<?php

    require_once '../config/config.php';
    require_once '../functions/security.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'reset.submit.php') {
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

    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Vérification du token CSRF pour éviter les attaques CSRF
        if (isset($_POST['csrf_token'])) {
            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
                http_response_code(403); 
                $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
                header('Location: /?page=reset'); 
                exit;
            }
        } else {
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=reset'); 
            exit;
        }

        // Récupérer le mot de passe saisi
        $token = $_POST['token'] ?? ''; 
        // Récupérer le mot de passe saisi
        $newPassword = $_POST['password'] ?? ''; 
        // Récupérer la confirmation du mot de passe
        $confirmPassword = $_POST['confirm_password'] ?? ''; 

        // Définition de la politique de mot de passe
        $passwordPolicy = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{12,}$/';

        if (empty($newPassword) || empty($confirmPassword)) {
            // Vérifier si les champs sont remplis
            $_SESSION['error_message'] = 'Please fill in both password fields.';
        } elseif ($newPassword !== $confirmPassword) {
            // Vérifier si les deux mots de passe sont identiques
            $_SESSION['error_message'] = 'Passwords do not match.';
        } elseif (!preg_match($passwordPolicy, $newPassword)) {
            // Vérifier si le mot de passe respecte la politique de sécurité
            $_SESSION['error_message'] = 'Password must contain, at least :
                12 characters
                - 1 uppercase letter
                - 1 lowercase letter
                - 1 number
                - 1 special character';
            
            header('Location: /?page=reset&token=' . $token);
            exit;
        } else {
            // Si toutes les conditions sont remplies, hacher le mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            try {
                // Récupérer l'utilisateur associé au token de réinitialisation sans exposer le token en clair
                $stmt = $pdo->prepare("SELECT id, email, reset_token, reset_token_expiry FROM users WHERE reset_token IS NOT NULL AND reset_token_expiry > NOW()");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $user = null;

                // Vérifier le token haché avec password_verify()
                foreach ($users as $row) {
                    if (password_verify($token, $row['reset_token'])) {
                        $user = $row;
                        break;
                    }
                }

                if (!$user) {
                    // Si aucun utilisateur correspondant n'est trouvé ou que le token est expiré
                    $_SESSION['error_message'] = 'This reset link is invalid or has expired.';
                    header('Location: /?page=login');
                    exit;
                }

                // Mettre à jour le mot de passe dans la base de données et réinitialiser le token
                $stmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
                $stmt->execute(['password' => $hashedPassword, 'id' => $user['id']]);

                // Afficher un message de succès et rediriger vers la page de connexion
                $_SESSION['success_message'] = 'Your password has been reset successfully. You can now log in.';
                header('Location: /?page=login');
                exit;
            } catch (Exception $e) {
                // En cas d'erreur lors de la mise à jour
                http_response_code(500);
                $_SESSION['error_message'] = 'Server error.';
                header('Location: /?page=login');
                exit;
            }
        }
    }
