<!DOCTYPE html>

<?php

// Charger la configuration et les fonctions nécessaires
require '../config/config.php';
require '../functions/security.php';
require '../functions/routes.php'; // Charger les routes

// Récupérer la page ou l'action demandée dans l'URL
$page = !empty($_GET['page']) ? htmlspecialchars($_GET['page'], ENT_QUOTES, 'UTF-8') : 'home';
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8') : null;

// Vérifier si c'est une action (soumission de formulaire)
if ($action === 'contact.submit') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include '../actions/contact.submit.php';
        exit; // Terminer après le traitement de l'action
    } else {
        http_response_code(405); // Méthode non autorisée
        die('Méthode non autorisée');
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
