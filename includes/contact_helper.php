<?php

    // Récupérer les valeurs de la session si elles existent
    $name = isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : '';
    $email = isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : '';

    // Vérifie si un token CSRF est déjà défini en session
    if (!isset($_SESSION['csrf_token'])) {
        // Génère un nouveau token CSRF sous forme de chaîne hexadécimale de 32 octets
        // Ce token est utilisé pour protéger les formulaires contre les attaques de type CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

