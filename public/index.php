<!DOCTYPE html>

<?php

// Charger la configuration et les fonctions nécessaires
require_once '../config/config.php';
require_once '../config/routes.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.cookie_httponly", 0);
    session_start();
    session_regenerate_id(true);
}

// Récupération de la page demandée
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : null;

// Vérifier si c'est une action (soumission de formulaire)
if ($action) {
    $actionFile = "../actions/" . $action . ".php";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include $actionFile;
        exit; // Terminer après le traitement de l'action
    } else {
        http_response_code(405); // Méthode non autorisée
        die('Method not authorised');
    }
}

// Affichage ou non de la page demandée
function loadPage($page) {
    $file = "../pages/" . $page . ".php";
    
    // Vérification sur le path 
    if (strpos($page, '..') !== false || strpos($page, '/') !== false) {
        http_response_code(403);
        die('Access denied.');
    }

    if (file_exists($file)) {
        include $file;
    } else {
        http_response_code(404);
        include "../pages/404.php";
    }
}

// Charger la page demandée
loadPage($page);
