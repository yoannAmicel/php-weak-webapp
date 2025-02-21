<?php

    // Inclusion des fichiers de configuration et des dépendances
    require_once '../config/config.php';
    require '../vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Empêche l'accès direct au fichier (bonne pratique)
    if (basename($_SERVER['PHP_SELF']) === 'contact.submit.php') {
        header('HTTP/1.1 403 Forbidden');
        exit('Direct access to this file is not allowed.');
    }

    // Vérifie que la méthode HTTP utilisée est POST (pour éviter les accès non autorisés)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); 
        $_SESSION['error_message'] = "Method not authorized.";
        header('Location: /?page=contact'); 
        exit;
    }

    // S.Contact.4 - Vérification du token CSRF pour éviter les attaques CSRF
    if (isset($_POST['csrf_token'])) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
            http_response_code(403); 
            $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
            header('Location: /?page=contact'); 
            exit;
        }
    } else {
        http_response_code(403); 
        $_SESSION['error_message'] = "Request not authorized (CSRF failure)";
        header('Location: /?page=contact'); 
        exit;
    }

    // Validation et nettoyage des données du formulaire
    $name = htmlspecialchars(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES, 'UTF-8');
    $recaptchaToken = $_POST['g-recaptcha-response'] ?? null;

    // Vérifie que les champs obligatoires sont remplis
    if (!$name || !$email || !$message) {
        http_response_code(400); 
        $_SESSION['error_message'] = 'Please fill in all required fields.';
        header('Location: /?page=contact'); 
        exit;
    }

    // Validation plus stricte du nom (uniquement lettres et espaces)
    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $_SESSION['error_message'] = "Invalid name format.";
        header('Location: /?page=contact');
        exit;
    }

    // Vérification de la longueur du message (évite les abus)
    if (strlen($message) < 10 || strlen($message) > 500) {
        $_SESSION['error_message'] = "Your message must be between 10 and 500 characters.";
        header('Location: /?page=contact');
        exit;
    }



    // Récupération de l'adresse IP de l'utilisateur
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    try {
        // Vérifie le nombre de tentatives d'envoi de formulaire dans les 30 dernières minutes
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_attempts WHERE ip_address = ? AND created_at >= NOW() - INTERVAL 30 MINUTE");
        $stmt->execute([$ipAddress]);
        $attemptCount = $stmt->fetchColumn();

        // Limite de 2 soumissions en 30 minutes
        if ($attemptCount > 2) { 
            $_SESSION['error_message'] = 'You have reached the limit of 2 submissions in 30 minutes. Please try again later.';
            header('Location: /?page=contact');
            exit;
        }

        // Enregistre la tentative d'envoi dans la base de données
        $stmt = $pdo->prepare("INSERT INTO contact_attempts (ip_address, created_at) VALUES (?, NOW())");
        $stmt->execute([$ipAddress]);

        // Vérification du reCAPTCHA pour prévenir les robots spammeurs
        $recaptchaSecret = '6LeCetwqAAAAAMppfDnwj_uMd-XREqusJVGmyceN'; // Récupération de la clé secrète
        $recaptchaURL = 'https://www.google.com/recaptcha/api/siteverify';
        // Vérification du reCAPTCHA via cURL
        $ch = curl_init($recaptchaURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret' => $recaptchaSecret,
            'response' => $recaptchaToken
        ]));
        $recaptchaResponse = curl_exec($ch);
        curl_close($ch);
        $recaptchaData = json_decode($recaptchaResponse, true);

        // Vérifie si la validation du reCAPTCHA a échoué
        if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
            http_response_code(400);
            $_SESSION['error_message'] = 'Captcha validation failed.';
            header('Location: /?page=contact');
            exit;
        }

        // Envoi de l'email via PHPMailer
        $mail = new PHPMailer(true);

        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'avenix.contact@gmail.com'; // Adresse e-mail utilisée pour l'envoi
        $mail->Password = 'rwdn zved thce dszw'; // Récupération du mot de passe sécurisé
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Chiffrement TLS
        $mail->Port = 587; // Port SMTP

        // Configuration des destinataires
        $mail->setFrom('avenix.contact@gmail.com', 'Avenix Contact Form'); // Expéditeur
        $mail->addAddress('avenix.contact@gmail.com', 'Avenix Team'); // Destinataire
        $mail->addReplyTo($email, $name); // Adresse de réponse

        // Configuration du contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Request'; // Sujet de l'email
        $mail->Body = "
            <h3>New Contact Request</h3>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Message:</strong></p>
            <p>$message</p>
        "; // Contenu en HTML
        $mail->AltBody = "Name: $name\nEmail: $email\nMessage:\n$message"; // Contenu en texte brut

        // Envoi de l'email
        $mail->send();

        // Message de confirmation
        $_SESSION['success_message'] = 'Message sent successfully!';
        header('Location: /?page=contact');
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        $_SESSION['error_message'] = 'An error occurred. Please try again later.';
        header('Location: /?page=contact');
        exit;
    }

