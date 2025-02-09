<?php
require_once '../config/config.php';
require_once '../functions/security.php';
require '../vendor/autoload.php'; // Charger PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Récupération de l'email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    $_SESSION['error_message'] = 'Please enter a valid email address.';
    header('Location: /?page=forgot-password');
    exit;
}

try {
    // Générer un token de réinitialisation, même si l'email n'existe pas
    $token = bin2hex(random_bytes(32));

    // Vérifier si l'utilisateur existe et mettre à jour le token uniquement si l'email est valide
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
            WHERE email = :email
        ");
        $stmt->execute(['token' => $token, 'email' => $email]);
    }

    // Préparer le lien de réinitialisation avec le format correct
    $resetLink = "http://localhost/index.php?page=reset&token=$token";

    // Envoyer l'email avec PHPMailer (simulé même si l'utilisateur n'existe pas)
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'avenix.contact@gmail.com'; // Ton adresse Gmail
        $mail->Password = 'rwdn zved thce dszw'; // Ton mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Paramètres de l'email
        $mail->setFrom('avenix.contact@gmail.com', 'Avenix');
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset Request';
        $mail->isHTML(true);
        $mail->Body = "
            <h3>Password Reset Request</h3>
            <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
            <p><a href='$resetLink'>$resetLink</a></p>
        ";
        $mail->AltBody = "Click the link to reset your password: $resetLink";

        // Envoyer l'email (ou simuler si l'utilisateur n'existe pas)
        $mail->send();
    } catch (Exception $e) {
        // Ignorer les erreurs d'envoi pour que l'utilisateur ne sache pas si l'email existe ou non
    }

    // Toujours afficher un message de succès
    $_SESSION['flash_message'] = 'If the email address is associated with an account, a password reset link has been sent.';
    header('Location: /?page=forgot-password');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    $_SESSION['error_message'] = 'An unexpected error occurred. Please try again later.';
    header('Location: /?page=forgot-password');
    exit;
}
