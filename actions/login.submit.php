<?php
require_once '../config/config.php';
require_once '../functions/security.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Récupération des données
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

if (!$email || !$password) {
    $_SESSION['error'] = 'Please fill in all fields.';
    header('Location: /?page=login');
    exit;
}

// Rechercher l'utilisateur dans la base de données
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: /?page=login');
        exit;
    }

    // Démarrer la session utilisateur
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email']
    ];
    header('Location: /?page=home');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    die('Server error: ' . $e->getMessage());
}
