<?php

    require_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'reset_helper.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // S.Reset.2 - Génère un token CSRF unique pour chaque session utilisateur
    // Il sera utilisé pour vérifier que la requête POST vient bien du formulaire et non d'un attaquant
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || time() - $_SESSION['csrf_token_time'] > 1800) { // Expire après 30 min
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    // Vérifier si un token est passé dans l'URL
    $token = $_GET['token'] ?? null;

    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        // S.Reset.1 - Validation du format du token
        $_SESSION['error_message'] = 'Invalid token format.';
        header('Location: /?page=login');
        exit;
    } else if (!$token) {
        // Si aucun token n'est fourni
        $_SESSION['error_message'] = 'Invalid or missing token.';
        header('Location: /?page=login');
        exit;
    }

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

    } catch (Exception $e) {
        // En cas d'erreur serveur, renvoyer un code HTTP 500 et afficher un message d'erreur
        http_response_code(500);
        $_SESSION['error_message'] = 'Server error.';
        header('Location: /?page=login');
        exit;
    }
