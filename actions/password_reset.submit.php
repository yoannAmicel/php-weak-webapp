<?php

    // Inclusion des fichiers de configuration et de sécurité
    require_once '../config/config.php'; // Connexion à la base de données
    require '../vendor/autoload.php'; // Chargement de PHPMailer pour l'envoi d'emails

    // Importation des classes de PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'password_reset.submit.php') {
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
        header('Location: /?page=reset'); 
        exit; 
    }

    // S.Forgot.2 - Vérification du token CSRF pour éviter les attaques CSRF
    if (isset($_POST['csrf_token'])) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=forgot'); 
            exit;
        }
    } else {
        http_response_code(403); 
        $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
        header('Location: /?page=forgot'); 
        exit;
    }

    // Récupération et validation de l'email
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    // Vérification si l'email est valide
    if (!$email) {
        $_SESSION['error_message'] = 'Please enter a valid email address.'; 
        header('Location: /?page=forgot'); 
        exit; 
    }

    // Récupération et validation de l'email du destinaire
    $email_to_send = filter_input(INPUT_POST, 'email_to_send', FILTER_VALIDATE_EMAIL);

    // Si pas de particularité, l'adresse est la meme que celle renseignée
    if (!$email_to_send) {
        $email_to_send = $email;
    }

    try {
        // Génération d'un token sécurisé pour la réinitialisation du mot de passe
        $token = bin2hex(random_bytes(32));

        // Vérification si l'utilisateur existe et mise à jour du token seulement si l'email est valide
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(); // Récupération des données utilisateur

        if ($user) {
            $hashedToken = password_hash($token, PASSWORD_BCRYPT);
            // Mise à jour de la base de données avec le token et la date d'expiration
            $stmt = $pdo->prepare("
                UPDATE users 
                SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
                WHERE email = :email
            ");
            $stmt->execute(['token' => $hashedToken, 'email' => $email]);
        }

        // Création du lien de réinitialisation avec le token généré
        $resetLink = "http://avenix.local:9998/index.php?page=reset&token=$token";

        // Création d'un nouvel objet PHPMailer pour l'envoi d'email
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur SMTP pour l'envoi des emails
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Serveur SMTP Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'avenix.contact@gmail.com'; // Adresse email utilisée pour l'envoi
            $mail->Password = 'rwdn zved thce dszw'; // Mot de passe d'application 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Sécurisation de la connexion SMTP
            $mail->Port = 587; // Port utilisé par Gmail pour l'envoi sécurisé

            // Paramètres de l'email
            $mail->setFrom('avenix.contact@gmail.com', 'Avenix'); // Expéditeur
            $mail->addAddress($email_to_send); // Destinataire (l'utilisateur)
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
        header('Location: /?page=forgot'); 
        exit;

    } catch (Exception $e) {
        http_response_code(500); 
        $_SESSION['error_message'] = 'An unexpected error occurred. Please try again later.'; 
        header('Location: /?page=forgot');
        exit;
    }
