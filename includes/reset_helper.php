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
