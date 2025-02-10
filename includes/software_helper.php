<?php 

    // Inclusion du fichier de configuration
    require_once '../config/config.php'; 

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si la connexion PDO est bien définie
    if (!isset($pdo)) {
        $_SESSION['error_message'] = 'Database connection is not defined.';
        header('Location: /?page=error');
        exit;
    }

    // Récupération de la liste des softwares
    try {
        $query = $pdo->query("SELECT * FROM software");
        $softwareItems = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Gestion d'erreur si la récupération des données échoue
        $_SESSION['error_message'] = 'An error occurred while fetching the data.';
        header('Location: /?page=software');
        exit;
    }
