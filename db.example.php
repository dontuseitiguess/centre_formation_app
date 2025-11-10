<?php
// Copiez ce fichier en db.php puis adaptez $user/$pass selon votre XAMPP.
// Sous XAMPP par défaut : user 'root', mot de passe '' (vide).

$host = 'localhost';
$db   = 'centre_formation';
$user = 'root';
$pass = ''; // mettre votre mot de passe si vous en avez défini un
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);
