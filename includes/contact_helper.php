<?php

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'contact_helper.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // Récupérer les valeurs de la session si elles existent
    $name = isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : '';
    $email = isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : '';

    // S.Contact.4 - Génération d'un token CSRF sécurisé avec expiration
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || time() - $_SESSION['csrf_token_time'] > 1800) { // Expire après 30 min
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }



