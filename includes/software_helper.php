<?php 

    // Inclusion du fichier de configuration
    require_once '../config/config.php'; 

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'software_helper.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || time() - $_SESSION['csrf_token_time'] > 1800) { // Expire après 30 min
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    // Vérifier si la connexion PDO est bien définie
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=error');
        exit;
    }

    // Récupération de la liste des softwares
    try {
        $stmt = $pdo->prepare("SELECT * FROM software");
        $stmt->execute();
        $softwareItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Gestion d'erreur si la récupération des données échoue
        error_log("Retrieving user data error: " . $e->getMessage(), 3, '../logs/error.log');
        $_SESSION['error_message'] = 'An error occurred while fetching the data.';
        header('Location: /?page=software');
        exit;
    }
