<?php

    include_once '../config/config.php';
    global $pdo;

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'validation_helper.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Vérifier si la connexion PDO est bien définie
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=error');
        exit;
    }

    // Vérification que l'utilisateur soit bien administrateur
    if (!isset($_SESSION['user']) || !hasPermission('admin', $pdo)) {
        // Redirection sur la page d'accueil s'il n'est pas admin
        $_SESSION['error_message'] = "You're not allowed to access this page.";
        header('Location: /?page=login'); 
        exit;
    }

    // Génération d'un jeton CSRF
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || time() - $_SESSION['csrf_token_time'] > 1800) { // Expire après 30 min
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    // Récupération des news en attente de validation
    try {
        $stmt = $pdo->query("SELECT * FROM news WHERE status = 'pending'");
        $pendingNews = $stmt->fetchAll(PDO::FETCH_ASSOC); // Stocke toutes les news en attente
    } catch (PDOException $e) {
        $pendingNews = []; // Si une erreur survient, on assigne un tableau vide pour éviter des erreurs d'affichage
        error_log("Retrieving pending news error: " . $e->getMessage(), 3, '../logs/error.log');   
        $_SESSION['error_message'] = 'Error fetching pending news.';
        header('Location: /?page=validation'); 
        exit;
    }

