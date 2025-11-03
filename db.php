<?php
// db.php : connexion à MySQL avec PDO
$host = 'localhost';
$db   = 'centre_formation';   // le nom de ta base
$user = 'root';
$pass = '';                   // sur XAMPP, mot de passe vide par défaut
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreurs visibles
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // tableaux associatifs
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  // si ça plante, on affiche un message clair
  die('Erreur de connexion à la base : ' . $e->getMessage());
}
