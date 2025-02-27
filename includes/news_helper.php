<?php

    // Inclusion de la configuration
    require_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'news_helper.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // S.News.2 - Génère un token CSRF unique pour chaque session utilisateur
    // Il sera utilisé pour vérifier que la requête POST vient bien du formulaire et non d'un attaquant
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || time() - $_SESSION['csrf_token_time'] > 1800) { // Expire après 30 min
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    // Vérification de la connexion à la base de données
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=news');
        exit;
    }

    // Récupération de tous les logiciels depuis la base de données
    // Utilisé pour l'ajout d'une news (possibilite de renseigner un software)
    try {
        $query = $pdo->query("SELECT * FROM software");
        $softwareItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetching software data error: " . $e->getMessage(), 3, '../logs/error.log');
        $_SESSION['error_message'] = 'Error fetching software data.';
        header('Location: /?page=news');
        exit;
    }

    // Récupération des news approuvées avec le nombre de commentaires associés
    try {
        $query = $pdo->query("
            SELECT n.*, COUNT(nc.id) AS comments_count
            FROM news n
            LEFT JOIN news_comment nc ON n.id = nc.news_id
            WHERE n.status = 'approved'
            GROUP BY n.id
        ");
        $newsItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Retrieving approved news error: " . $e->getMessage(), 3, '../logs/error.log');
        $_SESSION['error_message'] = 'Error fetching approved news.';
        header('Location: /?page=news');
        exit;
    }

    // Récupération de tous les commentaires
    try {
        $commentQuery = $pdo->query("SELECT * FROM news_comment");
        $comments = $commentQuery->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Retrieving comments error: " . $e->getMessage(), 3, '../logs/error.log');
        $_SESSION['error_message'] = 'Error fetching comments.';
        header('Location: /?page=news');
        exit;
    }

    // Gestion de la pagination
    $itemsPerPage = 5; // Nombre de news affichées par page
    $pageNumber = isset($_GET['page_number']) ? max(1, intval($_GET['page_number'])) : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    try {
        // Requête pour récupérer les news approuvées avec pagination
        $stmt = $pdo->prepare("
            SELECT n.*, COUNT(nc.id) AS comments_count
            FROM news n
            LEFT JOIN news_comment nc ON n.id = nc.news_id
            WHERE n.status = 'approved'
            GROUP BY n.id
            ORDER BY n.published_date DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupération du nombre total de news approuvées pour gérer la pagination
        $countStmt = $pdo->query("
            SELECT COUNT(*) 
            FROM news 
            WHERE status = 'approved'
        ");
        $totalNews = $countStmt->fetchColumn();
        $totalPages = ceil($totalNews / $itemsPerPage);
    } catch (PDOException $e) {
        error_log("Retrieving approved news error: " . $e->getMessage(), 3, '../logs/error.log');
        $_SESSION['error_message'] = 'Error fetching approved news.';
        $newsItems = [];
        $totalPages = 1;
    }

