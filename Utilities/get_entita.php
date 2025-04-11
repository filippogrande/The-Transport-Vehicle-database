<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'dbconnect.php'; // Connessione al database con PDO

$tabella = $_GET['tabella'] ?? null;

if (!$tabella) {
    echo json_encode([]);
    exit;
}

$query = "";
switch ($tabella) {
    case 'modello':
        $query = "SELECT id_modello AS id, nome FROM modello ORDER BY nome ASC";
        break;
    case 'veicolo':
        $query = "SELECT id_veicolo AS id, numero_targa AS nome FROM veicolo ORDER BY numero_targa ASC";
        break;
    case 'azienda_operatrice':
        $query = "SELECT id_azienda_operatrice AS id, nome_azienda AS nome FROM azienda_operatrice ORDER BY nome_azienda ASC";
        break;
    case 'azienda_costruttrice':
        $query = "SELECT id_azienda_costruttrice AS id, nome_azienda AS nome FROM azienda_costruttrice ORDER BY nome_azienda ASC";
        break;
    default:
        echo json_encode([]);
        exit;
}

try {
    $stmt = $pdo->query($query);
    $entita = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($entita);
} catch (PDOException $e) {
    echo json_encode([]);
}
