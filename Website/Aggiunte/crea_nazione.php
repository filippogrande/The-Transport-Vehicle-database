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
    $codice_iso = isset($_POST['codice_iso']) ? trim($_POST['codice_iso']) : null;
    $codice_iso2 = isset($_POST['codice_iso2']) ? trim($_POST['codice_iso2']) : null;
    $continente = isset($_POST['continente']) ? trim($_POST['continente']) : null;
    $capitale = isset($_POST['capitale']) ? trim($_POST['capitale']) : null;

    // Verifica che il nome della nazione sia stato fornito
    if (empty($nome)) {
        die("Errore: Il nome della nazione è obbligatorio.");
    }

    // Creazione della cartella per l'immagine
    $cartella = "../Photo/nazione/" . strtolower(str_replace(' ', '_', $nome)) . "/";
    if (!is_dir($cartella)) {
        if (!mkdir($cartella, 0777, true)) {
            die("Errore: impossibile creare la cartella per la bandiera.");
        }
    }

    // Controllo se è stato caricato un file
    if (isset($_FILES['bandiera']) && $_FILES['bandiera']['error'] === UPLOAD_ERR_OK) {
        $nome_file = basename($_FILES['bandiera']['name']);
        $percorso_destinazione = $cartella . $nome_file;

        // Controlla se il file è un'immagine
        $estensione = strtolower(pathinfo($percorso_destinazione, PATHINFO_EXTENSION));
        $tipi_consentiti = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($estensione, $tipi_consentiti)) {
            die("Errore: Formato file non valido. Sono ammessi solo JPG, PNG, GIF.");
        }

        // Sposta il file nella cartella di destinazione
        if (move_uploaded_file($_FILES['bandiera']['tmp_name'], $percorso_destinazione)) {
            $bandiera_url = "/Photo/nazione/" . strtolower(str_replace(' ', '_', $nome)) . "/" . $nome_file;
        } else {
            die("Errore nel caricamento del file. Verifica i permessi della directory.");
        }
    } else {
        $bandiera_url = null; // Se l'utente non carica un'immagine
    }

    // ID gruppo modifica per tracciabilità
    $id_gruppo_modifica = rand(1000, 9999);

    // Inseriamo i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'nazione', 'nome', :nome, 'In attesa', 'admin')";

        // Aggiungi i campi opzionali solo se sono stati forniti
        if (!empty($codice_iso)) {
            $query .= ", (:id_gruppo_modifica, 'nazione', 'codice_iso', :codice_iso, 'In attesa', 'admin')";
        }
        if (!empty($codice_iso2)) {
            $query .= ", (:id_gruppo_modifica, 'nazione', 'codice_iso2', :codice_iso2, 'In attesa', 'admin')";
        }
        if (!empty($continente)) {
            $query .= ", (:id_gruppo_modifica, 'nazione', 'continente', :continente, 'In attesa', 'admin')";
        }
        if (!empty($capitale)) {
            $query .= ", (:id_gruppo_modifica, 'nazione', 'capitale', :capitale, 'In attesa', 'admin')";
        }
        if (!empty($bandiera_url)) {
            $query .= ", (:id_gruppo_modifica, 'nazione', 'bandiera', :bandiera, 'In attesa', 'admin')";
        }

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
        $stmt->bindParam(':nome', $nome);
        if (!empty($codice_iso)) {
            $stmt->bindParam(':codice_iso', $codice_iso);
        }
        if (!empty($codice_iso2)) {
            $stmt->bindParam(':codice_iso2', $codice_iso2);
        }
        if (!empty($continente)) {
            $stmt->bindParam(':continente', $continente);
        }
        if (!empty($capitale)) {
            $stmt->bindParam(':capitale', $capitale);
        }
        if (!empty($bandiera_url)) {
            $stmt->bindParam(':bandiera', $bandiera_url);
        }

        $stmt->execute();

        echo "La nazione è stata proposta con successo. In attesa di approvazione.";
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
    <title>Crea Nazione</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Nuova Nazione</h1>

    <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="nome" class="form-label">Nome della Nazione</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
    </div>
    <div class="mb-3">
        <label for="codice_iso" class="form-label">Codice ISO (Alpha-3) (ITA)</label>
        <input type="text" class="form-control" id="codice_iso" name="codice_iso" maxlength="3">
    </div>
    <div class="mb-3">
        <label for="codice_iso2" class="form-label">Codice ISO (Alpha-2) (IT)</label>
        <input type="text" class="form-control" id="codice_iso2" name="codice_iso2" maxlength="2">
    </div>
    <div class="mb-3">
        <label for="continente" class="form-label">Continente</label>
        <input type="text" class="form-control" id="continente" name="continente">
    </div>
    <div class="mb-3">
        <label for="capitale" class="form-label">Capitale</label>
        <input type="text" class="form-control" id="capitale" name="capitale">
    </div>
    <div class="mb-3">
        <label for="bandiera" class="form-label">Carica Bandiera</label>
        <input type="file" class="form-control" id="bandiera" name="bandiera" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Proponi Nazione</button>
    <!-- Bottone per andare alla pagina Nazioni -->
    <a href="../nazioni.php" class="btn btn-secondary">Torna alle Nazioni</a>
</form>

</body>
</html>

