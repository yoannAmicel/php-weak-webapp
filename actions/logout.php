<?php

    // Charger la configuration
    require_once '../config/config.php';

    // Démarrer ou reprendre la session
    session_start();

    // Supprimer toutes les données de la session
    $_SESSION = [];

    // Détruire la session
    session_destroy();

    // Rediriger l'utilisateur vers la page de connexion
    $_SESSION['success_message'] = "You're disconnected.";
    header('Location: /?page=login');
    exit;