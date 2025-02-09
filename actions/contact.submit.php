<?php

    require_once '../config/config.php';
    require '../vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Vérifier que la méthode HTTP est POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Méthode non autorisée
        die('Method not authorized');
    }

    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403); // Accès interdit
        die('Request not authorized (CSRF failure)');
    }

    // Valider les champs du formulaire
    $name = htmlspecialchars(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS), ENT_QUOTES, 'UTF-8');
    $recaptchaToken = $_POST['g-recaptcha-response'] ?? null;

    if (!$name || !$email || !$message) {
        http_response_code(400); // Mauvaise requête
        $_SESSION['error_message'] = 'Please fill in all required fields.';
        header('Location: /?page=contact');
        exit;
    }

    // Récupérer l'adresse IP de l'utilisateur
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Connexion à la base de données
    global $pdo;

    try {
        // Vérifier le nombre de tentatives dans les 30 dernières minutes
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_attempts WHERE ip_address = ? AND created_at >= NOW() - INTERVAL 30 MINUTE");
        $stmt->execute([$ipAddress]);
        $attemptCount = $stmt->fetchColumn();

        if ($attemptCount >= 3) {
            $_SESSION['error_message'] = 'You have reached the limit of 2 submissions in 30 minutes. Please try again later.';
            header('Location: /?page=contact');
            exit;
        }

        // Enregistrer la tentative dans la base de données
        $stmt = $pdo->prepare("INSERT INTO contact_attempts (ip_address, created_at) VALUES (?, NOW())");
        $stmt->execute([$ipAddress]);

        // Vérifier reCAPTCHA
        $recaptchaSecret = getVaultSecret("apps/data/avenix/captcha", "private_key"); // Clé secrète reCAPTCHA
        $recaptchaURL = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptchaResponse = file_get_contents("$recaptchaURL?secret=$recaptchaSecret&response=$recaptchaToken");
        $recaptchaData = json_decode($recaptchaResponse, true);

        if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
            http_response_code(400);
            die('Captcha validation failed.');
        }

        // Envoi de l'email avec PHPMailer
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'avenix.contact@gmail.com';
        $mail->Password = getVaultSecret("apps/data/avenix/google", "app_password");
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('avenix.contact@gmail.com', 'Avenix Contact Form');
        $mail->addAddress('avenix.contact@gmail.com', 'Avenix Team');
        $mail->addReplyTo($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'New Contact Request';
        $mail->Body = "
            <h3>New Contact Request</h3>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Message:</strong></p>
            <p>$message</p>
        ";
        $mail->AltBody = "Name: $name\nEmail: $email\nMessage:\n$message";

        $mail->send();

        $_SESSION['flash_message'] = 'Message sent successfully!';
        header('Location: /?page=contact');
        exit;
    } catch (Exception $e) {
        http_response_code(500); // Erreur serveur
        $_SESSION['error_message'] = 'An error occurred. Please try again later.';
        header('Location: /?page=contact');
        exit;
    }
