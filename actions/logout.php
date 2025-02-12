<?php

    // Charger la configuration
    require_once '../config/config.php';

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Supprimer toutes les données de la session
    $_SESSION = [];

    // Détruire la session
    session_destroy();

    // Rediriger l'utilisateur vers la page de connexion
    $_SESSION['success_message'] = "You're disconnected.";
    header('Location: /?page=login');
    exit;