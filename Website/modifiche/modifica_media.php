<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

$id_media = $_GET['id'] ?? null;

if (!$id_media) {
    die("Errore: ID del media non fornito.");
}

try {
    // Recupera i dettagli del media
    $query = "SELECT * FROM media WHERE id_media = :id_media";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_media', $id_media, PDO::PARAM_INT);
    $stmt->execute();
    $media = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$media) {
        die("Errore: Media non trovato.");
    }
} catch (PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $tipo_media = $_POST['tipo_media'] ?? $media['tipo_media'];
    $descrizione = trim($_POST['descrizione'] ?? $media['descrizione']);
    $copyright = trim($_POST['copyright'] ?? $media['copyright']);
    $licenza = $_POST['licenza'] ?? $media['licenza'];

    // Verifica se è stato caricato un nuovo file
    $nuovo_file = isset($_FILES['file_media']) && $_FILES['file_media']['error'] === UPLOAD_ERR_OK;

    if ($nuovo_file) {
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
        $tipi_validi = ($tipo_media === 'Immagine') ? ['jpg', 'jpeg', 'png', 'gif', 'heic'] :
                       (($tipo_media === 'Video') ? ['mp4', 'webm', 'ogg'] : ['pdf', 'doc', 'docx', 'txt']);

        if (!in_array($estensione, $tipi_validi)) {
            die("Errore: Formato file non valido per il tipo di media selezionato.");
        }

        // Gestione dei file HEIC
        if ($estensione === 'heic') {
            $nome_file_senza_estensione = pathinfo($nome_file, PATHINFO_FILENAME);
            $percorso_destinazione = $base_dir . $nome_file_senza_estensione . '.jpeg';

            // Converti il file HEIC in JPEG
            if (!convertiHeicInJpeg($_FILES['file_media']['tmp_name'], $percorso_destinazione)) {
                die("Errore nella conversione del file HEIC in JPEG.");
            }
        } else {
            // Sposta il file nella directory di destinazione
            if (!move_uploaded_file($_FILES['file_media']['tmp_name'], $percorso_destinazione)) {
                die("Errore nel salvataggio del file. Verifica i permessi della directory.");
            }
        }
    } else {
        $percorso_destinazione = $media['url_media']; // Mantieni il file esistente
    }

    // ID gruppo modifica per tracciabilità
    $id_gruppo_modifica = rand(1000, 9999);

    // Inserisci i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, id_entita, campo_modificato, valore_nuovo, valore_vecchio, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'media', :id_entita, :campo_modificato, :valore_nuovo, :valore_vecchio, 'In attesa', 'admin')";
        $stmt = $pdo->prepare($query);

        $campi = [
            'tipo_media' => [$tipo_media, $media['tipo_media']],
            'url_media' => [$percorso_destinazione, $media['url_media']],
            'descrizione' => [$descrizione, $media['descrizione']],
            'copyright' => [$copyright, $media['copyright']],
            'licenza' => [$licenza, $media['licenza']],
        ];

        foreach ($campi as $campo => [$valore_nuovo, $valore_vecchio]) {
            if ($valore_nuovo !== $valore_vecchio) {
                $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
                $stmt->bindParam(':id_entita', $id_media, PDO::PARAM_INT);
                $stmt->bindParam(':campo_modificato', $campo);
                $stmt->bindParam(':valore_nuovo', $valore_nuovo);
                $stmt->bindParam(':valore_vecchio', $valore_vecchio);
                $stmt->execute();
            }
        }

        echo "Le modifiche al media sono state proposte con successo. In attesa di approvazione.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento della modifica: " . $e->getMessage();
    }

    // Inserisci i collegamenti alle entità nella tabella `modifiche_in_sospeso`
    $entita_collegate = $_POST['entita_collegate'] ?? [];
    try {
        foreach ($entita_collegate as $entita) {
            $query_entita = "
                INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                VALUES (:id_gruppo_modifica, 'media_entita', :campo_modificato, :valore_nuovo, 'In attesa', 'admin')
            ";
            $stmt_entita = $pdo->prepare($query_entita);

            $valore_nuovo = json_encode([
                'id_media' => $id_media,
                'entita_tipo' => $entita['entita_tipo'],
                'id_entita' => $entita['id_entita'],
                'ruolo' => $entita['ruolo']
            ]);

            $stmt_entita->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
            $stmt_entita->bindParam(':campo_modificato', $entita['entita_tipo']);
            $stmt_entita->bindParam(':valore_nuovo', $valore_nuovo);
            $stmt_entita->execute();
        }
    } catch (PDOException $e) {
        echo "Errore nell'inserimento dei collegamenti alle entità: " . $e->getMessage();
    }
}

/**
 * Converte un file HEIC in JPEG.
 *
 * @param string $inputPath Percorso del file HEIC di input.
 * @param string $outputPath Percorso del file JPEG di output.
 * @return bool True se la conversione ha successo, False altrimenti.
 */
function convertiHeicInJpeg($inputPath, $outputPath) {
    if (!extension_loaded('imagick')) {
        die("Errore: L'estensione Imagick non è abilitata sul server.");
    }

    try {
        $imagick = new Imagick();
        $imagick->readImage($inputPath);
        $imagick->setImageFormat('jpeg');
        $imagick->writeImage($outputPath);
        $imagick->clear();
        $imagick->destroy();
        return true;
    } catch (Exception $e) {
        error_log("Errore nella conversione HEIC: " . $e->getMessage());
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Media</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        async function aggiornaEntitaDropdown(index) {
            const tipoEntita = document.getElementById(`entita_tipo_${index}`).value;
            const dropdown = document.getElementById(`entita_id_${index}`);

            if (!tipoEntita) {
                dropdown.innerHTML = '<option value="">Seleziona un\'entità</option>';
                return;
            }

            try {
                const response = await fetch(`/Utilities/get_entita.php?tabella=${tipoEntita}`);
                const entita = await response.json();

                dropdown.innerHTML = '<option value="">Seleziona un\'entità</option>';
                entita.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.nome;
                    dropdown.appendChild(option);
                });
            } catch (error) {
                console.error('Errore nel caricamento delle entità:', error);
            }
        }
    </script>
</head>
<body class="container mt-4">
    <h1 class="mb-3">Modifica Media</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="tipo_media" class="form-label">Tipo di Media</label>
            <select class="form-control" id="tipo_media" name="tipo_media" required>
                <option value="Immagine" <?= $media['tipo_media'] === 'Immagine' ? 'selected' : '' ?>>Immagine</option>
                <option value="Video" <?= $media['tipo_media'] === 'Video' ? 'selected' : '' ?>>Video</option>
                <option value="Documento" <?= $media['tipo_media'] === 'Documento' ? 'selected' : '' ?>>Documento</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="file_media" class="form-label">File</label>
            <input type="file" class="form-control" id="file_media" name="file_media">
            <p>File attuale: <a href="<?= htmlspecialchars($media['url_media']) ?>" target="_blank"><?= htmlspecialchars($media['url_media']) ?></a></p>
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="3"><?= htmlspecialchars($media['descrizione'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="copyright" class="form-label">Copyright</label>
            <input type="text" class="form-control" id="copyright" name="copyright" value="<?= htmlspecialchars($media['copyright'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="licenza" class="form-label">Licenza</label>
            <select class="form-control" id="licenza" name="licenza" required>
                <option value="CC BY" <?= $media['licenza'] === 'CC BY' ? 'selected' : '' ?>>CC BY</option>
                <option value="CC BY-SA" <?= $media['licenza'] === 'CC BY-SA' ? 'selected' : '' ?>>CC BY-SA</option>
                <option value="CC BY-ND" <?= $media['licenza'] === 'CC BY-ND' ? 'selected' : '' ?>>CC BY-ND</option>
                <option value="CC BY-NC" <?= $media['licenza'] === 'CC BY-NC' ? 'selected' : '' ?>>CC BY-NC</option>
                <option value="CC BY-NC-SA" <?= $media['licenza'] === 'CC BY-NC-SA' ? 'selected' : '' ?>>CC BY-NC-SA</option>
                <option value="CC BY-NC-ND" <?= $media['licenza'] === 'CC BY-NC-ND' ? 'selected' : '' ?>>CC BY-NC-ND</option>
                <option value="CC0" <?= $media['licenza'] === 'CC0' ? 'selected' : '' ?>>CC0</option>
                <option value="CC PDM" <?= $media['licenza'] === 'CC PDM' ? 'selected' : '' ?>>CC PDM</option>
            </select>
        </div>

        <h3 class="mt-4">Collega Media a Entità</h3>
        <div id="entita-container">
            <div class="entita-row mb-3">
                <label for="entita_tipo_1" class="form-label">Tipo di Entità</label>
                <select class="form-control" id="entita_tipo_1" name="entita_collegate[0][entita_tipo]" onchange="aggiornaEntitaDropdown(1)" required>
                    <option value="">Seleziona un tipo</option>
                    <option value="modello">Modello</option>
                    <option value="veicolo">Veicolo</option>
                    <option value="azienda_operatrice">Azienda Operatrice</option>
                    <option value="azienda_costruttrice">Azienda Costruttrice</option>
                </select>
                <label for="entita_id_1" class="form-label mt-2">Entità</label>
                <select class="form-control" id="entita_id_1" name="entita_collegate[0][id_entita]" required>
                    <option value="">Seleziona un'entità</option>
                </select>
                <label for="entita_ruolo_1" class="form-label mt-2">Ruolo</label>
                <input type="text" class="form-control" id="entita_ruolo_1" name="entita_collegate[0][ruolo]" placeholder="Es. Proprietario, Costruttore">
            </div>
        </div>
        <button type="button" class="btn btn-secondary mb-3" onclick="aggiungiEntita()">Aggiungi Collegamento</button>

        <button type="submit" class="btn btn-primary">Proponi Modifiche</button>
        <a href="../visualizza_media.php" class="btn btn-secondary">Annulla</a>
    </form>

    <script>
        let entitaCounter = 1;

        function aggiungiEntita() {
            const container = document.getElementById('entita-container');
            const newRow = document.createElement('div');
            newRow.className = 'entita-row mb-3';
            newRow.innerHTML = `
                <label for="entita_tipo_${entitaCounter}" class="form-label">Tipo di Entità</label>
                <select class="form-control" id="entita_tipo_${entitaCounter}" name="entita_collegate[${entitaCounter}][entita_tipo]" onchange="aggiornaEntitaDropdown(${entitaCounter})" required>
                    <option value="">Seleziona un tipo</option>
                    <option value="modello">Modello</option>
                    <option value="veicolo">Veicolo</option>
                    <option value="azienda_operatrice">Azienda Operatrice</option>
                    <option value="azienda_costruttrice">Azienda Costruttrice</option>
                </select>
                <label for="entita_id_${entitaCounter}" class="form-label mt-2">Entità</label>
                <select class="form-control" id="entita_id_${entitaCounter}" name="entita_collegate[${entitaCounter}][id_entita]" required>
                    <option value="">Seleziona un'entità</option>
                </select>
                <label for="entita_ruolo_${entitaCounter}" class="form-label mt-2">Ruolo</label>
                <input type="text" class="form-control" id="entita_ruolo_${entitaCounter}" name="entita_collegate[${entitaCounter}][ruolo]" placeholder="Es. Proprietario, Costruttore">
            `;
            container.appendChild(newRow);
            entitaCounter++;
        }
    </script>
</body>
</html>
