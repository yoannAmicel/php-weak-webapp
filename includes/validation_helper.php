<?php

    include_once '../config/config.php';
    global $pdo;

    // Vérification que l'utilisateur soit bien administrateur
    if (!isset($_SESSION['user']) || !hasPermission('admin', $pdo)) {
        // Redirection sur la page d'accueil s'il n'est pas admin
        $_SESSION['error_message'] = "You're not allowed to access this page.";
        header('Location: /?page=login'); 
        exit;
    }

    // Récupération des news en attente de validation
    try {
        $stmt = $pdo->query("SELECT * FROM news WHERE status = 'pending'");
        $pendingNews = $stmt->fetchAll(PDO::FETCH_ASSOC); // Stocke toutes les news en attente
    } catch (PDOException $e) {
        $pendingNews = []; // Si une erreur survient, on assigne un tableau vide pour éviter des erreurs d'affichage
        $_SESSION['error_message'] = 'Error fetching pending news.';
        header('Location: /?page=validation'); 
        exit;
    }

