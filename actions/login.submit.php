<?php

    // Inclusion du fichier de configuration 
    require_once '../config/config.php';
    // Inclusion du fichier de sécurité 
    require_once '../functions/security.php';

    // Démarre une session si aucune n'est active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérification que la requête est bien une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); 
        $_SESSION['error_message'] = 'Method not allowed'; 
        header('Location: /?page=login'); 
        exit; 
    }

    // Récupération et validation des données du formulaire
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'); // Vérifie et filtre l'email
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8'); // Nettoie la valeur du mot de passe

    // Vérification que tous les champs sont remplis
    if (!$email || !$password) {
        $_SESSION['error_message'] = 'Please fill in all fields.'; 
        header('Location: /?page=login'); 
        exit; 
    }


    
    // Recherche de l'utilisateur dans la base de données
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email"); 
        $stmt->execute(['email' => $email]); 
        $user = $stmt->fetch(); 

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

        // Redirection vers la page d'accueil après connexion réussie
        header('Location: /?page=home');
        exit; 

    } catch (Exception $e) {
        http_response_code(500); 
        $_SESSION['error_message'] = 'Server error.'; 
        header('Location: /?page=login'); 
        exit; 
    }
