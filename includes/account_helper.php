<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
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
        $_SESSION['error_message'] = "Error retrieving user data.";
        header('Location: /?page=account');
        exit;
    }
