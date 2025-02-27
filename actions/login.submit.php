<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'login.submit.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // Vérifie que la connexion à la base de données est bien initialisée
    if (!isset($pdo)) {
        // Renvoie un message d'erreur si la variable n'est pas initialisée
        $_SESSION['error_message'] = "Error: Database connection not defined.";
        header('Location: /?page=home');
        exit;
    }

    // Vérification que la requête est bien une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); 
        $_SESSION['error_message'] = 'Method not allowed'; 
        header('Location: /?page=login'); 
        exit; 
    }

    // S.Login.4 - Vérification du token CSRF pour éviter les attaques CSRF
    if (isset($_POST['csrf_token'])) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=login'); 
            exit;
        }
    } else {
        http_response_code(403); 
        $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
        header('Location: /?page=login'); 
        exit;
    }

    // S.Login.1 - Récupération et validation des données du formulaire
    $email = $_POST['email']; // Vérifie et filtre l'email
    $password = trim($_POST['password']);  // Nettoie la valeur du mot de passe

    // Vérification que tous les champs sont remplis
    if (!$email || !$password) {
        $_SESSION['error_message'] = 'Please fill in all fields.'; 
        header('Location: /?page=login'); 
        exit; 
    }

    
    // Recherche de l'utilisateur dans la base de données
    try {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $stmt = $pdo->query($query);
        $user = $stmt->fetch(); 

        // S.Login.3 - Vérifier que le compte n’est pas banni
        if ($user['is_blocked']) {
            $_SESSION['error_message'] = 'Your account has been locked. Contact support.';
            header('Location: /?page=login');
            exit;
        }
        


        // Vérification si l'utilisateur existe et si le mot de passe est correct
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error_message'] = 'Invalid email or password.'; 
            header('Location: /?page=login'); 
            exit; 
        }

        // Si l'utilisateur est authentifié, on stocke ses informations dans la session
        $_SESSION['user'] = [
            'id' => $user['id'],       // ID utilisateur
            'name' => $user['name'],   // Nom utilisateur
            'email' => $user['email']  // Email utilisateur
        ];

        // S.Login.6 - Empêche le vol de session si un attaquant vole un cookie de connexion.
        session_regenerate_id(true);


        
        // Ajout d'un log en cas de connexion réussie
        $logFile = '../logs/auth.log';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Login successful - ID: " . $user['id'] . " - Email: " . $user['email'] . " - Username: " . $user['name'] . " - IP: " . $ipAddress . PHP_EOL;

        // Vérifie si le dossier de logs existe, sinon le créer
        if (file_exists('../logs')) {
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        } 


        // Redirection vers la page d'accueil après connexion réussie
        header('Location: /?page=home');
        exit; 

    } catch (Exception $e) {
        http_response_code(500); 
        $_SESSION['error_message'] = 'Server error.'; 
        header('Location: /?page=login'); 
        exit; 
    }
