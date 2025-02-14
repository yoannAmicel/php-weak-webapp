<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'users_helper.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Vérifier si la connexion PDO est bien définie
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=error');
        exit;
    }

    // Vérification et démarrage de la session si nécessaire
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // Vérifie si l'utilisateur est administrateur, sinon redirige vers la page d'accueil
    if (!isset($_SESSION['user']) || !hasPermission('admin', $pdo)) {
        header('Location: /?page=home');
        exit;
    }

    // Génération d'un jeton CSRF
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || time() - $_SESSION['csrf_token_time'] > 1800) { // Expire après 30 min
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    // Gestion de la pagination
    $itemsPerPage = 10; // Nombre d'utilisateurs affichés par page
    $pageNumber = isset($_GET['page_number']) ? max(1, intval($_GET['page_number'])) : 1;
    $pageNumber = filter_var($_GET['page_number'] ?? 1, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) ?: 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    // Récupération des utilisateurs avec pagination
    try {
        // Récupération du nombre total d'utilisateurs pour la pagination
        $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
        $totalUsers = $countStmt->fetchColumn();
        $totalPages = ceil($totalUsers / $itemsPerPage);
    } catch (PDOException $e) {
        $users = [];
        $totalPages = 1;
        error_log("Retrieving user data error: " . $e->getMessage(), 3, '../logs/error.log');
        $_SESSION['error_message'] = 'Error fetching users.';
        header('Location: /?page=users');
        exit;
    }

    // Récupération des statistiques utilisateur (uniquement sur la première page)
    if ($pageNumber === 1) {
        try {
            $totalUsers = $pdo->query("SELECT COUNT(*) AS count FROM users")->fetchColumn();
            $totalAdmins = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetchColumn();
            $totalUsersRole = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetchColumn();
            $totalGuests = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'guest'")->fetchColumn();
            $totalBlocked = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE is_blocked = 1")->fetchColumn();
        } catch (PDOException $e) {
            error_log("Retrieving statistics error: " . $e->getMessage(), 3, '../logs/error.log');
            $_SESSION['error_message'] = 'Error fetching statistics.';
            header('Location: /?page=users');
            exit;
        }
    }

    // Gestion du tri des colonnes
    $validSortColumns = ['name', 'email', 'role']; // Colonnes valides pour le tri
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $validSortColumns, true) ? $_GET['sort'] : 'name';
    $order = isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc'], true) ? strtolower($_GET['order']) : 'asc';

    try {
        // Requête sécurisée avec tri et pagination
        $query = "
            SELECT id, name, email, role, is_blocked 
            FROM users 
            ORDER BY $sort $order 
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':limit', (int) $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log et message d'erreur en cas d'échec
        error_log("Retrieving user data error: " . $e->getMessage(), 3, '../logs/error.log');
        $_SESSION['error_message'] = 'Error fetching users.';
        header('Location: /?page=users');
        exit;
    }


