<?php

    require_once '../config/config.php';
    require_once '../functions/security.php';

    // Vérifier si un token est passé dans l'URL
    $token = $_GET['token'] ?? null;

    if (!$token) {
        // Si aucun token n'est fourni
        $_SESSION['error_message'] = 'Invalid or missing token.';
        header('Location: /?page=login');
        exit;
    }

    try {
        // Récupérer l'utilisateur associé au token de réinitialisation et vérifier s'il est encore valide
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            // Si aucun utilisateur correspondant n'est trouvé ou que le token est expiré
            $_SESSION['error_message'] = 'This reset link is invalid or has expired.';
            header('Location: /?page=login');
            exit;
        }
    } catch (Exception $e) {
        // En cas d'erreur serveur, renvoyer un code HTTP 500 et afficher un message d'erreur
        http_response_code(500);
        $_SESSION['error_message'] = 'Server error.';
        header('Location: /?page=login');
        exit;
    }



    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer le mot de passe saisi
        $newPassword = $_POST['password'] ?? ''; 
        // Récupérer la confirmation du mot de passe
        $confirmPassword = $_POST['confirm_password'] ?? ''; 

        // Définition de la politique de mot de passe
        $passwordPolicy = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{12,}$/';

        // Vérifier si les champs sont remplis
        if (empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error_message'] = 'Please fill in both password fields.';
        }
        // Vérifier si les deux mots de passe sont identiques
        elseif ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = 'Passwords do not match.';
        }
        // Vérifier si le mot de passe respecte la politique de sécurité
        elseif (!preg_match($passwordPolicy, $newPassword)) {
            $_SESSION['error_message'] = 'Password must contain, at least :
                12 characters
                - 1 uppercase letter
                - 1 lowercase letter
                - 1 number
                - 1 special character';
        }
        else {
            // Si toutes les conditions sont remplies, hacher le mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            try {
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
