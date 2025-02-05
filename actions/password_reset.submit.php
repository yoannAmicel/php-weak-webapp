<?php
require_once '../config/config.php';
require_once '../functions/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Récupération de l'email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header('Location: /pages/forgot_password.php');
    exit;
}

// Vérifier si l'utilisateur existe
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = 'Email not found.';
        header('Location: /pages/forgot_password.php');
        exit;
    }

    // Générer un token de réinitialisation
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email");
    $stmt->execute(['token' => $token, 'email' => $email]);

    // Envoyer l'email de réinitialisation
    $resetLink = "http://yourdomain.com/pages/reset_password.php?token=$token";
    mail($email, "Password Reset", "Click this link to reset your password: $resetLink");

    $_SESSION['success'] = 'A password reset link has been sent to your email.';
    header('Location: /pages/login.php');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    die('Server error: ' . $e->getMessage());
}
