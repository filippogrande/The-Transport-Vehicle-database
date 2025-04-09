<?php
require_once __DIR__ . '/../vendor/autoload.php'; // torna indietro di una cartella

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // stesso percorso del .env
$dotenv->load();

$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

$dsn = "pgsql:host=$host;dbname=$dbname";
try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Errore di connessione: " . $e->getMessage();
}
?>
