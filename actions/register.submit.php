<?php

    // Inclusion des fichiers de configuration et de sécurité
    require_once '../config/config.php'; // Connexion à la base de données

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'register.submit.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
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

    // S.Register.2 - Vérification du token CSRF pour éviter les attaques CSRF
    if (isset($_POST['csrf_token'])) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=forgot-password'); 
            exit;
        }
    } else {
        http_response_code(403); 
        $_SESSION['error_message'] = "Request not authorized (CSRF failure) ici";
        header('Location: /?page=forgot-password'); 
        exit;
    }

    // Récupération et validation des données du formulaire
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'); // Nettoie le nom 
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'); // Vérifie et nettoie l'email
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8'); // Nettoie le mot de passe
    $password_confirmation = htmlspecialchars($_POST['password_confirmation'], ENT_QUOTES, 'UTF-8'); // Vérifie la confirmation du mot de passe

    // Vérification que tous les champs sont remplis
    if (!$name || !$email || !$password || !$password_confirmation) {
        $_SESSION['error_message'] = 'Please fill in all fields.'; 
        header('Location: /?page=register'); 
        exit; 
    }

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    // Validation plus stricte du nom (uniquement lettres et espaces)
    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $_SESSION['error_message'] = "Invalid name format.";
        header('Location: /?page=contact');
        exit;
    }

    // Vérification que les deux mots de passe correspondent
    if ($password !== $password_confirmation) {
        $_SESSION['error_message'] = 'Passwords do not match.'; 
        header('Location: /?page=register'); 
        exit;
    }

    // Politique de mot de passe : Vérification de la complexité
    $passwordPolicy = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{12,}$/';
    if (!preg_match($passwordPolicy, $password)) {
        $_SESSION['error_message'] = 'Password must contain, at least :
                12 characters
                - 1 uppercase letter
                - 1 lowercase letter
                - 1 number
                - 1 special character';
        header('Location: /?page=register'); // Redirection
        exit;
    }

    // Vérification si l'email existe déjà en base de données
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email"); 
        $stmt->execute(['email' => $email]); 
        if ($stmt->fetch()) { 
            // Si un résultat est trouvé, l'email est déjà utilisé
            $_SESSION['error_message'] = 'Email already exists.'; 
            header('Location: /?page=register'); 
            exit;
        }

        // S.Login.3 - Vérifier que le compte n’est pas banni
        if ($user['is_blocked']) {
            $_SESSION['error_message'] = 'Your account has been locked. Contact support.';
            header('Location: /?page=login');
            exit;
        }

        // Empêche les attaques bruteforce sur les mots de passe
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND created_at >= NOW() - INTERVAL 15 MINUTE");
        $stmt->execute([$ipAddress]);
        $attemptCount = $stmt->fetchColumn();
        
        // Bloque après 5 essais en 15 minutes
        if ($attemptCount >= 5) {
            $_SESSION['error_message'] = 'Too many login attempts. Try again later.';
            header('Location: /?page=login');
            exit;
        }
        
        // Ajout d'une tentative
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, created_at) VALUES (?, NOW())");
        $stmt->execute([$ipAddress]);


        // Hachage du mot de passe avant l'insertion en base de données
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Ajout du nouvel utilisateur dans la base de données
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashedPassword]);

        // Message de succès et redirection vers la page de connexion
        $_SESSION['success_message'] = 'Account created successfully. You can now log in.';
        header('Location: /?page=login');
        exit;

    } catch (Exception $e) {
        // Gestion des erreurs internes du serveur
        http_response_code(500); 
        $_SESSION['error_message'] = 'Server error.';
        header('Location: /?page=register');
        exit;
    }

