<?php
    require_once '../config/config.php'; 
    // Inclusion des routes et éventuelles fonctions globales
    require_once '../config/routes.php'; 

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'header_helper.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    
    // S.Contact.3
    header("X-Frame-Options: DENY"); // Protège contre le Clickjacking
    header("X-Content-Type-Options: nosniff"); // Empêche les types MIME incorrects
    header("Referrer-Policy: no-referrer-when-downgrade"); // Restriction sur l'envoi du referer
    // header("Content-Security-Policy: default-src 'self'"); // Empêche les scripts externes dangereux (suppression due à Tailwind) 

    // Déclaration explicite de $pdo en global pour accéder à la connexion à la base de données
    global $pdo;
    
    // Vérifie si une session est active avant d'en démarrer une nouvelle 
    // Objectif : protection contre les CSRF
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // Vérification de la connexion à la base de données
    if (!isset($pdo)) {
        // Renvoie d'une erreur en cas d'absence de connexion PDO
        $_SESSION['error_message'] = 'Error: Database connection not defined.';
        header('Location: /?page=home');
        exit;
    }


    // Initialise la variable indiquant si l'utilisateur est administrateur
    $isAdmin = false;

    // Vérifie si un utilisateur est connecté en testant la présence de son ID dans la session
    if (isset($_SESSION['user']['id'])) {
        // Récupère l'ID de l'utilisateur depuis la session
        $userId = $_SESSION['user']['id'];
        
        // Récupérer la photo de profil & le rôle de l'utilisateur
        $stmt = $pdo->prepare("SELECT profile_picture, role FROM users WHERE id = :id");
        
        // Lie l'ID utilisateur stocké en session au paramètre de la requête SQL
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        
        // Exécute la requête SQL
        $stmt->execute();
        
        // Récupère le résultat sous forme de tableau 
        $result = $stmt->fetch();

        // Vérifie si un résultat a été trouvé dans la base de données
        if ($result) {
            // Récupère l'URL de la photo de profil tout en sécurisant l'affichage avec htmlspecialchars
            $profilePicture = htmlspecialchars($result['profile_picture'] ?? '');
            
            // Vérifie si le rôle de l'utilisateur est "admin" et met à jour la variable correspondante
            $isAdmin = $result['role'] === 'admin';
        }
    }


    // Récupérer le nombre de news en attente de validation
    $pendingCount = 0; // Valeur par défaut si aucune news en attente

    // Vérifie si l'utilisateur est connecté et possède les permissions administrateur
    if (isset($_SESSION['user']) && hasPermission('admin', $pdo)) {
        
        try {
            // Compter le nombre de news avec le statut "pending"
            $stmt = $pdo->query("SELECT COUNT(*) AS pending_count FROM news WHERE status = 'pending'");
            
            // Récupère le résultat sous forme de tableau 
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Assigne le nombre de news en attente, ou 0 si aucun résultat
            $pendingCount = $result['pending_count'] ?? 0;

        } catch (PDOException $e) {
            // En cas d'erreur de requête SQL, on affiche 0 news en attente
            $pendingCount = 0;
            
            // Renvoie un message d'erreur si la requête n'aboutie pas
            error_log("Pending news count error: " . $e->getMessage(), 3, '../logs/error.log');
            $_SESSION['error_message'] = "Error: Unable to retrieve pending news count.";
            header('Location: /?page=home');
            exit;
        }
    }