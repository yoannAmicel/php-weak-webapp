<!DOCTYPE html>

<?php

// Charger la configuration et les fonctions nécessaires
require_once '../config/config.php';
require_once '../functions/security.php';
require_once '../functions/routes.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer la page ou l'action demandée dans l'URL
$page = !empty($_GET['page']) ? htmlspecialchars($_GET['page'], ENT_QUOTES, 'UTF-8') : 'home';
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8') : null;

// Vérifier si c'est une action (soumission de formulaire)
if (isset($action)) {
    $actionFile = "../actions/" . $action . ".php";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include $actionFile;
        exit; // Terminer après le traitement de l'action
    } else {
        http_response_code(405); // Méthode non autorisée
        die('Method not autorised');
    }
}


// Vérifier si le fichier demandé existe dans les routes
function loadPage($page) {
    global $routes;
    if (isset($routes[$page]) && file_exists("../pages/" . $routes[$page])) {
        include "../pages/" . $routes[$page];
    } else {
        http_response_code(404);
        include "../pages/404.php";
    }
}

// Charger la page demandée
loadPage($page);
