<?php

    // Inclusion des fichiers de configuration et de sécurité
    require_once '../config/config.php'; // Connexion à la base de données
    require_once '../functions/security.php'; // Fonctions de sécurité supplémentaires

    // Vérification que la requête est bien une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); 
        $_SESSION['error_message'] = 'Method not allowed'; 
        header('Location: /?page=login'); 
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

