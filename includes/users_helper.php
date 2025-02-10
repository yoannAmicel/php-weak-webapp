<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';

    // Vérification et démarrage de la session si nécessaire
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifie si l'utilisateur est administrateur, sinon redirige vers la page d'accueil
    if (!isset($_SESSION['user']) || !hasPermission('admin', $pdo)) {
        header('Location: /?page=home');
        exit;
    }

    // Gestion de la pagination
    $itemsPerPage = 10; // Nombre d'utilisateurs affichés par page
    $pageNumber = isset($_GET['page_number']) ? max(1, intval($_GET['page_number'])) : 1;
    $offset = ($pageNumber - 1) * $itemsPerPage;

    // Récupération des utilisateurs avec pagination
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, role, is_blocked FROM users LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupération du nombre total d'utilisateurs pour la pagination
        $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
        $totalUsers = $countStmt->fetchColumn();
        $totalPages = ceil($totalUsers / $itemsPerPage);
    } catch (PDOException $e) {
        $users = [];
        $totalPages = 1;
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
            $_SESSION['error_message'] = 'Error fetching statistics.';
            header('Location: /?page=users');
            exit;
        }
    }

    // Gestion du tri des colonnes
    $validSortColumns = ['name', 'email', 'role']; // Colonnes valides pour le tri
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $validSortColumns) ? $_GET['sort'] : 'name';
    $order = isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc']) ? $_GET['order'] : 'asc';

    // Récupération des utilisateurs avec tri et pagination
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, email, role, is_blocked 
            FROM users 
            ORDER BY $sort $order 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $users = [];
        $_SESSION['error_message'] = 'Error fetching users.';
        header('Location: /?page=users');
        exit;
    }

