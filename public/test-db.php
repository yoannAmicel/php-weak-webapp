<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'test';
$user = 'root';
$pass = 'a';

// Connexion à la base de données (procédural)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

try {
    $link = mysqli_connect($host, $user, $pass, $dbname);

    if (!$link) {
        die('Erreur de connexion à la base de données : ' . mysqli_connect_error());
    }

    echo '<h3>Connexion réussie !</h3>';
    echo '<p>Base de données actuelle : ' . htmlspecialchars($dbname) . '</p>';
} catch (Exception $e) {
    die('<h3>Erreur lors de la connexion :</h3><p>' . htmlspecialchars($e->getMessage()) . '</p>');
}
