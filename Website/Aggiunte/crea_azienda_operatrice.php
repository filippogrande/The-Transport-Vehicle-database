<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $nome_azienda = trim($_POST['nome_azienda']);
    $nome_precedente = isset($_POST['nome_precedente']) ? trim($_POST['nome_precedente']) : null;
    $sede_legale = isset($_POST['sede_legale']) ? trim($_POST['sede_legale']) : null;
    $città = isset($_POST['città']) ? trim($_POST['città']) : null;
    $paese = isset($_POST['paese']) ? trim($_POST['paese']) : null;
    $numero_telefono = isset($_POST['numero_telefono']) ? trim($_POST['numero_telefono']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $data_inizio_attività = isset($_POST['data_inizio_attività']) ? trim($_POST['data_inizio_attività']) : null;
    $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : null;
    $stato_azienda = isset($_POST['stato_azienda']) ? trim($_POST['stato_azienda']) : 'Attiva';

    // Verifica che il nome dell'azienda sia stato fornito
    if (empty($nome_azienda)) {
        die("Errore: Il nome dell'azienda è obbligatorio.");
    }

    // Creazione della cartella per il logo
    $cartella = "../Photo/azienda_operatrice/" . strtolower(str_replace(' ', '_', $nome_azienda)) . "/";
    if (!is_dir($cartella)) {
        if (!mkdir($cartella, 0777, true)) {
            die("Errore: impossibile creare la cartella per il logo.");
        }
    }

    // Controllo se è stato caricato un file
    if (isset($_FILES['foto_logo']) && $_FILES['foto_logo']['error'] === UPLOAD_ERR_OK) {
        $nome_file = basename($_FILES['foto_logo']['name']);
        $percorso_destinazione = $cartella . $nome_file;

        // Controlla se il file è un'immagine
        $estensione = strtolower(pathinfo($percorso_destinazione, PATHINFO_EXTENSION));
        $tipi_consentiti = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($estensione, $tipi_consentiti)) {
            die("Errore: Formato file non valido. Sono ammessi solo JPG, PNG, GIF.");
        }

        // Sposta il file nella cartella di destinazione
        if (move_uploaded_file($_FILES['foto_logo']['tmp_name'], $percorso_destinazione)) {
            $foto_logo_url = "/Photo/azienda_operatrice/" . strtolower(str_replace(' ', '_', $nome_azienda)) . "/" . $nome_file;
        } else {
            die("Errore nel caricamento del file. Verifica i permessi della directory.");
        }
    } else {
        $foto_logo_url = null; // Se l'utente non carica un'immagine
    }

    // ID gruppo modifica per raggruppare le modifiche
    $id_gruppo_modifica = rand(1000, 9999);

    // Inserisci i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'azienda_operatrice', :campo_modificato, :valore_nuovo, 'In attesa', 'admin')";
        $stmt = $pdo->prepare($query);

        $campi = [
            'nome_azienda' => $nome_azienda,
            'nome_precedente' => $nome_precedente,
            'sede_legale' => $sede_legale,
            'città' => $città,
            'paese' => $paese,
            'numero_telefono' => $numero_telefono,
            'email' => $email,
            'data_inizio_attività' => $data_inizio_attività,
            'descrizione' => $descrizione,
            'stato_azienda' => $stato_azienda,
            'foto_logo' => $foto_logo_url,
        ];

        foreach ($campi as $campo => $valore_nuovo) {
            if ($valore_nuovo !== null) {
                $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
                $stmt->bindParam(':campo_modificato', $campo);
                $stmt->bindParam(':valore_nuovo', $valore_nuovo);
                $stmt->execute();
            }
        }

        echo "L'azienda operatrice è stata proposta con successo. In attesa di approvazione.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento della proposta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Azienda Operatrice</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Nuova Azienda Operatrice</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nome_azienda" class="form-label">Nome dell'Azienda</label>
            <input type="text" class="form-control" id="nome_azienda" name="nome_azienda" required>
        </div>
        <div class="mb-3">
            <label for="nome_precedente" class="form-label">Nome Precedente</label>
            <input type="text" class="form-control" id="nome_precedente" name="nome_precedente">
        </div>
        <div class="mb-3">
            <label for="sede_legale" class="form-label">Sede Legale</label>
            <input type="text" class="form-control" id="sede_legale" name="sede_legale">
        </div>
        <div class="mb-3">
            <label for="città" class="form-label">Città</label>
            <input type="text" class="form-control" id="città" name="città">
        </div>
        <div class="mb-3">
            <label for="paese" class="form-label">Paese</label>
            <input type="text" class="form-control" id="paese" name="paese">
        </div>
        <div class="mb-3">
            <label for="numero_telefono" class="form-label">Numero di Telefono</label>
            <input type="text" class="form-control" id="numero_telefono" name="numero_telefono">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        <div class="mb-3">
            <label for="data_inizio_attività" class="form-label">Data Inizio Attività</label>
            <input type="date" class="form-control" id="data_inizio_attività" name="data_inizio_attività">
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="5"></textarea>
        </div>
        <div class="mb-3">
            <label for="foto_logo" class="form-label">Carica Logo</label>
            <input type="file" class="form-control" id="foto_logo" name="foto_logo" accept="image/*">
        </div>
        <div class="mb-3">
            <label for="stato_azienda" class="form-label">Stato dell'Azienda</label>
            <select class="form-control" id="stato_azienda" name="stato_azienda">
                <option value="Attiva">Attiva</option>
                <option value="Chiusa">Chiusa</option>
                <option value="Fallita">Fallita</option>
                <option value="Acquisita">Acquisita</option>
                <option value="Rinominata">Rinominata</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Proponi Azienda</button>
        <a href="../aziende_operatrici.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
