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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $nome = trim($_POST['nome']);
    $short_desc = isset($_POST['short_desc']) ? trim($_POST['short_desc']) : null;
    $long_desc = isset($_POST['long_desc']) ? trim($_POST['long_desc']) : null;
    $fondazione = isset($_POST['fondazione']) ? trim($_POST['fondazione']) : null;
    $chiusura = isset($_POST['chiusura']) ? trim($_POST['chiusura']) : null;
    $sede = isset($_POST['sede']) ? trim($_POST['sede']) : null;
    $nazione = isset($_POST['nazione']) ? trim($_POST['nazione']) : null;
    $sito_web = isset($_POST['sito_web']) ? trim($_POST['sito_web']) : null;
    $stato = isset($_POST['stato']) ? trim($_POST['stato']) : 'Attiva';

    // Verifica che il nome dell'azienda sia stato fornito
    if (empty($nome)) {
        die("Errore: Il nome dell'azienda è obbligatorio.");
    }

    // Creazione della cartella per il logo
    $cartella = "../Photo/azienda/" . strtolower(str_replace(' ', '_', $nome)) . "/";
    if (!is_dir($cartella)) {
        if (!mkdir($cartella, 0777, true)) {
            die("Errore: impossibile creare la cartella per il logo.");
        }
    }

    // Controllo se è stato caricato un file
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $nome_file = basename($_FILES['logo']['name']);
        $percorso_destinazione = $cartella . $nome_file;

        // Controlla se il file è un'immagine
        $estensione = strtolower(pathinfo($percorso_destinazione, PATHINFO_EXTENSION));
        $tipi_consentiti = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($estensione, $tipi_consentiti)) {
            die("Errore: Formato file non valido. Sono ammessi solo JPG, PNG, GIF.");
        }

        // Sposta il file nella cartella di destinazione
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $percorso_destinazione)) {
            $logo_url = "/Photo/azienda/" . strtolower(str_replace(' ', '_', $nome)) . "/" . $nome_file;
        } else {
            die("Errore nel caricamento del file. Verifica i permessi della directory.");
        }
    } else {
        $logo_url = null; // Se l'utente non carica un'immagine
    }

    // ID gruppo modifica per tracciabilità
    $id_gruppo_modifica = rand(1000, 9999);

    // Inseriamo i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'azienda_costruttrice', 'nome', :nome, 'In attesa', 'admin')";

        // Aggiungi i campi opzionali solo se sono stati forniti
        if (!empty($short_desc)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'short_desc', :short_desc, 'In attesa', 'admin')";
        }
        if (!empty($long_desc)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'long_desc', :long_desc, 'In attesa', 'admin')";
        }
        if (!empty($fondazione)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'fondazione', :fondazione, 'In attesa', 'admin')";
        }
        if (!empty($chiusura)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'chiusura', :chiusura, 'In attesa', 'admin')";
        }
        if (!empty($sede)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'sede', :sede, 'In attesa', 'admin')";
        }
        if (!empty($nazione)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'nazione', :nazione, 'In attesa', 'admin')";
        }
        if (!empty($sito_web)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'sito_web', :sito_web, 'In attesa', 'admin')";
        }
        if (!empty($stato)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'stato', :stato, 'In attesa', 'admin')";
        }
        if (!empty($logo_url)) {
            $query .= ", (:id_gruppo_modifica, 'azienda_costruttrice', 'logo', :logo, 'In attesa', 'admin')";
        }

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
        $stmt->bindParam(':nome', $nome);
        if (!empty($short_desc)) {
            $stmt->bindParam(':short_desc', $short_desc);
        }
        if (!empty($long_desc)) {
            $stmt->bindParam(':long_desc', $long_desc);
        }
        if (!empty($fondazione)) {
            $stmt->bindParam(':fondazione', $fondazione);
        }
        if (!empty($chiusura)) {
            $stmt->bindParam(':chiusura', $chiusura);
        }
        if (!empty($sede)) {
            $stmt->bindParam(':sede', $sede);
        }
        if (!empty($nazione)) {
            $stmt->bindParam(':nazione', $nazione);
        }
        if (!empty($sito_web)) {
            $stmt->bindParam(':sito_web', $sito_web);
        }
        if (!empty($stato)) {
            $stmt->bindParam(':stato', $stato);
        }
        if (!empty($logo_url)) {
            $stmt->bindParam(':logo', $logo_url);
        }

        $stmt->execute();

        echo "L'azienda è stata proposta con successo. In attesa di approvazione.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento della modifica: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Azienda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Nuova Azienda</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome dell'Azienda</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="mb-3">
            <label for="short_desc" class="form-label">Descrizione Breve</label>
            <input type="text" class="form-control" id="short_desc" name="short_desc">
        </div>
        <div class="mb-3">
            <label for="long_desc" class="form-label">Descrizione Lunga</label>
            <textarea class="form-control" id="long_desc" name="long_desc"></textarea>
        </div>
        <div class="mb-3">
            <label for="fondazione" class="form-label">Data di Fondazione</label>
            <input type="date" class="form-control" id="fondazione" name="fondazione">
        </div>
        <div class="mb-3">
            <label for="chiusura" class="form-label">Data di Chiusura (se applicabile) </label>
            <input type="date" class="form-control" id="chiusura" name="chiusura">
        </div>
        <div class="mb-3">
            <label for="sede" class="form-label">Sede</label>
            <input type="text" class="form-control" id="sede" name="sede">
        </div>
        <div class="mb-3">
            <label for="nazione" class="form-label">Nazione</label>
            <input type="text" class="form-control" id="nazione" name="nazione">
        </div>
        <div class="mb-3">
            <label for="sito_web" class="form-label">Sito Web</label>
            <input type="url" class="form-control" id="sito_web" name="sito_web">
        </div>
        <div class="mb-3">
            <label for="stato" class="form-label">Stato</label>
            <select class="form-control" id="stato" name="stato">
                <option value="Attiva">Attiva</option>
                <option value="Chiusa">Chiusa</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="logo" class="form-label">Carica Logo</label>
            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Proponi Azienda</button>
        <a href="../aziende.php" class="btn btn-secondary">Vai alle Aziende</a>
    </form>
</body>
</html>
