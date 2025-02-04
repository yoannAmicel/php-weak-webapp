<?php

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

if (!$name || !$email || !$message) {
    http_response_code(400); // Mauvaise requête
    die('Fill in all fields.');
}

// Simuler un traitement (ex. : envoi d'email ou sauvegarde dans la base de données)
try {
    // Créer le dossier logs s'il n'existe pas
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0777, true);
    }

    // Par exemple : Loguer dans un fichier (ou utiliser une base de données)
    $logEntry = sprintf("[%s] Name: %s, Email: %s, Message: %s\n", date('Y-m-d H:i:s'), $name, $email, $message);
    file_put_contents(__DIR__ . '/../logs/contact.log', $logEntry, FILE_APPEND);

    // Redirection avec un message de succès
    $_SESSION['flash_message'] = 'Message sent!';
    header('Location: /?page=contact');
    exit;
} catch (Exception $e) {
    http_response_code(500); // Erreur serveur
    die('An error occurred. Please try again later.');
}
