<?php

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', getVaultSecret("apps/data/avenix/database", "db_database"));
define('DB_USER', getVaultSecret("apps/data/avenix/database", "db_username"));
define('DB_PASS', getVaultSecret("apps/data/avenix/database", "db_password"));

// Activer les erreurs en mode développement
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Paramètres généraux de l'application
define('APP_NAME', 'Avenix');
define('APP_ENV', 'development'); // development, production
define('APP_URL', 'http://localhost');
define('APP_LOG_PATH', __DIR__ . '/../logs/app.log');

// Configurations liées à la sécurité
define('SESSION_LIFETIME', 3600); // Durée de session en secondes (1 heure)

// Initialiser une connexion PDO à la base de données
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Connexion to database failed : ' . $e->getMessage());
}

// Démarrer une session sécurisée
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
]);


function getVaultSecret($path, $key) {
    $vaultAddr = "https://127.0.0.1:8200"; 
    $vaultToken = "hvs.KrhhLvt05tqLHtSwlXUivx37"; 

    $url = "{$vaultAddr}/v1/{$path}";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Vault-Token: {$vaultToken}"    
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        die("Erreur : Impossible de récupérer les secrets (HTTP Code: $httpCode)");
    }

    curl_close($ch);

    $data = json_decode($response, true);
    return $data['data']['data'][$key] ?? null;
}


function hasPermission($requiredRole, $pdo) {

    // Si l'utilisateur n'est pas connecté, il est un "guest"
    if (!isset($_SESSION['user']['id'])) {
        return $requiredRole === 'guest';
    }

    // Récupérer le rôle de l'utilisateur connecté
    $userId = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return false; // Utilisateur introuvable
    }

    $userRole = $user['role'];

    // Définir la hiérarchie des rôles
    $roleHierarchy = ['user' => 0, 'admin' => 1];

    // Vérifier si l'utilisateur a un rôle suffisant
    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}
