<?php
// Abilita la visualizzazione degli errori durante lo sviluppo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Collegamento al database
require_once '../Utilities/dbconnect.php';

// Verifica la connessione al database
if (!$pdo) {
    die("Errore nella connessione al database.");
}

include '../header.html'; // Include l'header

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $nome = trim($_POST['nome']);
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : null;
    $anno_inizio_produzione = isset($_POST['anno_inizio_produzione']) ? trim($_POST['anno_inizio_produzione']) : null;
    $anno_fine_produzione = isset($_POST['anno_fine_produzione']) ? trim($_POST['anno_fine_produzione']) : null;
    $lunghezza = isset($_POST['lunghezza']) ? trim($_POST['lunghezza']) : null;
    $larghezza = isset($_POST['larghezza']) ? trim($_POST['larghezza']) : null;
    $altezza = isset($_POST['altezza']) ? trim($_POST['altezza']) : null;
    $peso = isset($_POST['peso']) ? trim($_POST['peso']) : null;
    $motorizzazione = isset($_POST['motorizzazione']) ? trim($_POST['motorizzazione']) : null;
    $velocita_massima = isset($_POST['velocita_massima']) ? trim($_POST['velocita_massima']) : null;
    $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : null;
    $totale_veicoli = isset($_POST['totale_veicoli']) ? trim($_POST['totale_veicoli']) : null;
    $posti_seduti = isset($_POST['posti_seduti']) ? trim($_POST['posti_seduti']) : 0;
    $posti_in_piedi = isset($_POST['posti_in_piedi']) ? trim($_POST['posti_in_piedi']) : 0;
    $posti_carrozzine = isset($_POST['posti_carrozzine']) ? trim($_POST['posti_carrozzine']) : 0;

    // Verifica che il nome del modello sia stato fornito
    if (empty($nome)) {
        die("Errore: Il nome del modello è obbligatorio.");
    }

    // Validazione dei campi numerici
    if ($lunghezza !== null && ($lunghezza < 0 || $lunghezza >= 10000)) {
        die("Errore: La lunghezza deve essere compresa tra 0 e 9999.99.");
    }
    if ($larghezza !== null && ($larghezza < 0 || $larghezza >= 10000)) {
        die("Errore: La larghezza deve essere compresa tra 0 e 9999.99.");
    }
    if ($altezza !== null && ($altezza < 0 || $altezza >= 10000)) {
        die("Errore: L'altezza deve essere compresa tra 0 e 9999.99.");
    }
    if ($peso !== null && ($peso < 0 || $peso >= 10000000000)) { // Aggiornato il limite per il peso
        die("Errore: Il peso deve essere compreso tra 0 e 9999999999.99.");
    }
    if ($velocita_massima !== null && ($velocita_massima < 0 || $velocita_massima >= 10000)) {
        die("Errore: La velocità massima deve essere compresa tra 0 e 9999.99.");
    }

    // Completa l'anno con una data predefinita
    if (!empty($anno_inizio_produzione)) {
        $anno_inizio_produzione .= '-01-01';
    }
    if (!empty($anno_fine_produzione)) {
        $anno_fine_produzione .= '-01-01';
    }

    // Inseriamo i dati nella tabella `modello`
    try {
        $query = "INSERT INTO modello (nome, tipo, anno_inizio_produzione, anno_fine_produzione, lunghezza, larghezza, altezza, peso, motorizzazione, velocita_massima, descrizione, totale_veicoli, posti_seduti, posti_in_piedi, posti_carrozzine) 
                  VALUES (:nome, :tipo, :anno_inizio_produzione, :anno_fine_produzione, :lunghezza, :larghezza, :altezza, :peso, :motorizzazione, :velocita_massima, :descrizione, :totale_veicoli, :posti_seduti, :posti_in_piedi, :posti_carrozzine)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':anno_inizio_produzione', $anno_inizio_produzione);
        $stmt->bindParam(':anno_fine_produzione', $anno_fine_produzione);
        $stmt->bindParam(':lunghezza', $lunghezza);
        $stmt->bindParam(':larghezza', $larghezza);
        $stmt->bindParam(':altezza', $altezza);
        $stmt->bindParam(':peso', $peso);
        $stmt->bindParam(':motorizzazione', $motorizzazione);
        $stmt->bindParam(':velocita_massima', $velocita_massima);
        $stmt->bindParam(':descrizione', $descrizione);
        $stmt->bindParam(':totale_veicoli', $totale_veicoli);
        $stmt->bindParam(':posti_seduti', $posti_seduti);
        $stmt->bindParam(':posti_in_piedi', $posti_in_piedi);
        $stmt->bindParam(':posti_carrozzine', $posti_carrozzine);

        $stmt->execute();

        echo "Il modello è stato creato con successo.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento del modello: " . $e->getMessage();
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Modello</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Nuovo Modello</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome del Modello</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select class="form-control" id="tipo" name="tipo">
                <option value="">Seleziona un tipo</option>
                <option value="Autobus">Autobus</option>
                <option value="Tram">Tram</option>
                <option value="Treno">Treno</option>
                <option value="Metro">Metro</option>
                <option value="Filobus">Filobus</option>
                <option value="Altro">Altro</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="anno_inizio_produzione" class="form-label">Anno Inizio Produzione</label>
            <input type="number" class="form-control" id="anno_inizio_produzione" name="anno_inizio_produzione" min="1900" max="2100">
        </div>
        <div class="mb-3">
            <label for="anno_fine_produzione" class="form-label">Anno Fine Produzione</label>
            <input type="number" class="form-control" id="anno_fine_produzione" name="anno_fine_produzione" min="1900" max="2100">
        </div>
        <div class="mb-3">
            <label for="lunghezza" class="form-label">Lunghezza (m)</label>
            <input type="number" step="0.01" class="form-control" id="lunghezza" name="lunghezza">
        </div>
        <div class="mb-3">
            <label for="larghezza" class="form-label">Larghezza (m)</label>
            <input type="number" step="0.01" class="form-control" id="larghezza" name="larghezza">
        </div>
        <div class="mb-3">
            <label for="altezza" class="form-label">Altezza (m)</label>
            <input type="number" step="0.01" class="form-control" id="altezza" name="altezza">
        </div>
        <div class="mb-3">
            <label for="peso" class="form-label">Peso (kg)</label>
            <input type="number" step="0.01" class="form-control" id="peso" name="peso">
        </div>
        <div class="mb-3">
            <label for="motorizzazione" class="form-label">Motorizzazione</label>
            <input type="text" class="form-control" id="motorizzazione" name="motorizzazione">
        </div>
        <div class="mb-3">
            <label for="velocita_massima" class="form-label">Velocità Massima (km/h)</label>
            <input type="number" step="0.01" class="form-control" id="velocita_massima" name="velocita_massima">
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="5" style="resize: vertical;"></textarea>
        </div>
        <div class="mb-3">
            <label for="totale_veicoli" class="form-label">Totale Veicoli Prodotti</label>
            <input type="number" class="form-control" id="totale_veicoli" name="totale_veicoli">
        </div>
        <div class="mb-3">
            <label for="posti_seduti" class="form-label">Posti Seduti</label>
            <input type="number" class="form-control" id="posti_seduti" name="posti_seduti" min="0" value="0">
        </div>
        <div class="mb-3">
            <label for="posti_in_piedi" class="form-label">Posti in Piedi</label>
            <input type="number" class="form-control" id="posti_in_piedi" name="posti_in_piedi" min="0" value="0">
        </div>
        <div class="mb-3">
            <label for="posti_carrozzine" class="form-label">Posti Carrozzine</label>
            <input type="number" class="form-control" id="posti_carrozzine" name="posti_carrozzine" min="0" value="0">
        </div>
        <button type="submit" class="btn btn-primary">Crea Modello</button>
        <a href="../modelli.php" class="btn btn-secondary">Torna alla pagina Modelli</a>
    </form>
</body>
</html>
