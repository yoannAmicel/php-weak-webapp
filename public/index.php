<!DOCTYPE html>

<?php

// Charger la configuration et les fonctions nécessaires
require '../config/config.php';
require '../functions/security.php';
require '../functions/routes.php'; // Charger les routes

// Récupérer la page demandée dans l'URL
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_STRING) : 'home';

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
