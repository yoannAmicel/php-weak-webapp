<?php
    require_once '../config/config.php';
    require_once '../functions/security.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die('Method not allowed');
    }

    // Récupération des données
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $password_confirmation = filter_input(INPUT_POST, 'password_confirmation', FILTER_SANITIZE_STRING);

    if (!$name || !$email || !$password || !$password_confirmation) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: /?page=register');
        exit;
    }

    if ($password !== $password_confirmation) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: /?page=register');
        exit;
    }

    // Vérifier si l'email existe déjà
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email already exists.';
            header('Location: /?page=register');
            exit;
        }

        // Ajouter l'utilisateur
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashedPassword]);

        $_SESSION['register_success'] = 'Account created successfully. You can now log in.';
        header('Location: /?page=login');
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        die('Server error: ' . $e->getMessage());
    }
