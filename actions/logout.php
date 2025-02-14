<?php

    // Charger la configuration
    require_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'logout.submit.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // Supprimer toutes les données de la session
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, 
            $params["path"], $params["domain"], 
            $params["secure"], $params["httponly"]
        );
    }    

    // Détruire la session
    session_destroy();

    // Message de confirmation pour l'utilisateur
    session_start(); // Redémarre une session pour stocker le message de déconnexion
    $_SESSION['success_message'] = "You're disconnected.";
    header('Location: /?page=login');
    exit;