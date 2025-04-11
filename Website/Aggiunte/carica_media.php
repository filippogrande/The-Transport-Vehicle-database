<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $tipo_media = $_POST['tipo_media'] ?? null;
    $descrizione = trim($_POST['descrizione'] ?? null);
    $copyright = trim($_POST['copyright'] ?? null);
    $licenza = $_POST['licenza'] ?? null;

    // Verifica che i campi obbligatori siano stati forniti
    if (empty($tipo_media) || empty($licenza) || !isset($_FILES['file_media'])) {
        die("Errore: Tutti i campi obbligatori devono essere compilati.");
    }

    // Verifica che il file sia stato caricato correttamente
    if ($_FILES['file_media']['error'] !== UPLOAD_ERR_OK) {
        die("Errore nel caricamento del file.");
    }

    // Determina la directory di destinazione in base al tipo di media
    $base_dir = ($tipo_media === 'Immagine') ? '../Photo/' : (($tipo_media === 'Video') ? '../Video/' : '../Documenti/');
    if (!is_dir($base_dir)) {
        if (!mkdir($base_dir, 0777, true)) {
            die("Errore: impossibile creare la directory di destinazione.");
        }
    }

    // Genera il percorso completo del file
    $nome_file = basename($_FILES['file_media']['name']);
    $percorso_destinazione = $base_dir . $nome_file;

    // Controlla se il file è valido in base al tipo di media
    $estensione = strtolower(pathinfo($percorso_destinazione, PATHINFO_EXTENSION));
    $tipi_validi = ($tipo_media === 'Immagine') ? ['jpg', 'jpeg', 'png', 'gif'] :
                   (($tipo_media === 'Video') ? ['mp4', 'webm', 'ogg'] : ['pdf', 'doc', 'docx', 'txt']);

    if (!in_array($estensione, $tipi_validi)) {
        die("Errore: Formato file non valido per il tipo di media selezionato.");
    }

    // Sposta il file nella directory di destinazione
    if (!move_uploaded_file($_FILES['file_media']['tmp_name'], $percorso_destinazione)) {
        die("Errore nel salvataggio del file. Verifica i permessi della directory.");
    }

    // Inserisci i dati nella tabella `media`
    try {
        $query = "
            INSERT INTO media (tipo_media, url_media, descrizione, copyright, licenza) 
            VALUES (:tipo_media, :url_media, :descrizione, :copyright, :licenza)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':tipo_media', $tipo_media);
        $stmt->bindParam(':url_media', $percorso_destinazione);
        $stmt->bindParam(':descrizione', $descrizione);
        $stmt->bindParam(':copyright', $copyright);
        $stmt->bindParam(':licenza', $licenza);
        $stmt->execute();

        echo "Il file è stato caricato con successo.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento dei dati: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carica Media</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Carica Media</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="tipo_media" class="form-label">Tipo di Media</label>
            <select class="form-control" id="tipo_media" name="tipo_media" required>
                <option value="">Seleziona un tipo</option>
                <option value="Immagine">Immagine</option>
                <option value="Video">Video</option>
                <option value="Documento">Documento</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="file_media" class="form-label">File</label>
            <input type="file" class="form-control" id="file_media" name="file_media" required>
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label for="copyright" class="form-label">Copyright</label>
            <input type="text" class="form-control" id="copyright" name="copyright">
        </div>
        <div class="mb-3">
            <label for="licenza" class="form-label">Licenza</label>
            <select class="form-control" id="licenza" name="licenza" required>
                <option value="">Seleziona una licenza</option>
                <option value="Pubblico dominio">Pubblico dominio</option>
                <option value="Creative Commons">Creative Commons</option>
                <option value="Proprietario">Proprietario</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Carica Media</button>
        <a href="../index.html" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
