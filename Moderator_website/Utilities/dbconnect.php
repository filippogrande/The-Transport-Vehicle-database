<?php
$host = 'localhost';  // O l'indirizzo IP del server PostgreSQL
$dbname = 'ttvd_db';  // Nome del database creato
$user = 'ttvd_user';  // Nome utente del database
$password = "7GMMg50Mhy";  // Password dell'utente del database

// Connessione con PDO
$dsn = "pgsql:host=$host;dbname=$dbname";
try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Errore di connessione: " . $e->getMessage();
}
?>
