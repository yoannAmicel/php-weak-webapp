<?php

$routes = [
    'home' => 'home.php',
    'software' => 'software.php',
    'news' => 'news.php',
    'contact' => 'contact.php',
    'login' => 'login.php',
    'register' => 'register.php',
    'forgot-password' => 'forgot.php',
    'legal' => 'legal_notice.php',
    'privacy' => 'privacy_policy.php',
    'myaccount' => 'myaccount.php',
    'validation' => 'validation.php',
    'users' => 'users.php',
    'reset' => 'reset.php'
];

function route($name) {
    global $routes;
    if (isset($routes[$name])) {
        return '/index.php?page=' . $name; // Génère une URL basée sur le paramètre "page"
    }
    return '/index.php?page=404'; 
}
