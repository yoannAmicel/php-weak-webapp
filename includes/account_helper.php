<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'account_helper.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // S.Account.1 - Génère un token CSRF unique pour chaque session utilisateur
    // Il sera utilisé pour vérifier que la requête POST vient bien du formulaire et non d'un attaquant
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère une chaîne sécurisée en hexadécimal
    }


    // Vérifie que la connexion à la base de données est bien initialisée
    if (!isset($pdo)) {
        // Renvoie un message d'erreur si la variable n'est pas initialisée
        $_SESSION['error_message'] = "Error: Database connection not defined.";
        header('Location: /?page=account');
        exit;
    }

    // Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
    if (!isset($_SESSION['user']['id'])) {
        header('Location: /?page=login');
        exit;
    }


    

    // Récupération des informations de l'utilisateur depuis la base de données
    // Utilisé pour l'affichage des données sur la page
    $userId = $_SESSION['user']['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error_message'] = "Error: User not found.";
            header('Location: /?page=account');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Retrieving user data error: " . $e->getMessage(), 3, '../logs/error.log');
        $_SESSION['error_message'] = "Error retrieving user data.";
        header('Location: /?page=account');
        exit;
    }
