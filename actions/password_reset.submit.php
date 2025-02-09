<?php

    // Inclusion des fichiers de configuration et de sécurité
    require_once '../config/config.php'; // Connexion à la base de données
    require_once '../functions/security.php'; // Fichier contenant des fonctions de sécurité
    require '../vendor/autoload.php'; // Chargement de PHPMailer pour l'envoi d'emails

    // Importation des classes de PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Vérification que la requête est bien une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); 
        $_SESSION['error_message'] = 'Method not allowed'; 
        header('Location: /?page=reset'); 
        exit; 
    }

    // Récupération et validation de l'email
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    // Vérification si l'email est valide
    if (!$email) {
        $_SESSION['error_message'] = 'Please enter a valid email address.'; 
        header('Location: /?page=forgot-password'); 
        exit; 
    }

    try {
        // Génération d'un token sécurisé pour la réinitialisation du mot de passe
        $token = bin2hex(random_bytes(32));

        // Vérification si l'utilisateur existe et mise à jour du token seulement si l'email est valide
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(); // Récupération des données utilisateur

        if ($user) {
            // Mise à jour de la base de données avec le token et la date d'expiration
            $stmt = $pdo->prepare("
                UPDATE users 
                SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
                WHERE email = :email
            ");
            $stmt->execute(['token' => $token, 'email' => $email]);
        }

        // Création du lien de réinitialisation avec le token généré
        $resetLink = "http://localhost/index.php?page=reset&token=$token";

        // Création d'un nouvel objet PHPMailer pour l'envoi d'email
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur SMTP pour l'envoi des emails
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Serveur SMTP Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'avenix.contact@gmail.com'; // Adresse email utilisée pour l'envoi
            $mail->Password = getVaultSecret("apps/data/avenix/google", "app_password"); // Mot de passe d'application 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Sécurisation de la connexion SMTP
            $mail->Port = 587; // Port utilisé par Gmail pour l'envoi sécurisé

            // Paramètres de l'email
            $mail->setFrom('avenix.contact@gmail.com', 'Avenix'); // Expéditeur
            $mail->addAddress($email); // Destinataire (l'utilisateur)
            $mail->Subject = 'Password Reset Request'; // Objet de l'email
            $mail->isHTML(true); // Activation du format HTML pour l'email
            $mail->Body = "
                <h3>Password Reset Request</h3>
                <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
                <p><a href='$resetLink'>$resetLink</a></p>
            ";
            $mail->AltBody = "Click the link to reset your password: $resetLink"; // Version texte brut

            // Envoi de l'email
            $mail->send();
        } catch (Exception $e) {
            // Ignorer les erreurs d'envoi d'email pour ne pas révéler si l'email existe ou non
        }

        // Toujours afficher un message de succès pour éviter de divulguer les emails existants
        $_SESSION['success_message'] = 'If the email address is associated with an account, a password reset link has been sent.';
        header('Location: /?page=forgot-password'); 
        exit;

    } catch (Exception $e) {
        http_response_code(500); 
        $_SESSION['error_message'] = 'An unexpected error occurred. Please try again later.'; 
        header('Location: /?page=forgot-password');
        exit;
    }
