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

    // Inserisci i dati nella tabella `modifiche_in_sospeso`
    try {
        $id_gruppo_modifica = rand(1000, 9999); // ID gruppo modifica per tracciabilità

        $query = "
            INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
            VALUES (:id_gruppo_modifica, 'media', :campo_modificato, :valore_nuovo, 'In attesa', 'admin')
        ";
        $stmt = $pdo->prepare($query);

        $campi = [
            'tipo_media' => $tipo_media,
            'url_media' => $percorso_destinazione,
            'descrizione' => $descrizione,
            'copyright' => $copyright,
            'licenza' => $licenza,
        ];

        foreach ($campi as $campo => $valore_nuovo) {
            if ($valore_nuovo !== null) {
                $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
                $stmt->bindParam(':campo_modificato', $campo);
                $stmt->bindParam(':valore_nuovo', $valore_nuovo);
                $stmt->execute();
            }
        }

        // Inserisci i collegamenti alle entità nella tabella `modifiche_in_sospeso`
        $entita_collegate = $_POST['entita_collegate'] ?? [];
        foreach ($entita_collegate as $entita) {
            $query_entita = "
                INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                VALUES (:id_gruppo_modifica, :tabella_destinazione, 'id_media', :id_entita, 'In attesa', 'admin')
            ";
            $stmt_entita = $pdo->prepare($query_entita);
            $stmt_entita->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
            $stmt_entita->bindParam(':tabella_destinazione', $entita['tabella']);
            $stmt_entita->bindParam(':id_entita', $entita['id']);
            $stmt_entita->execute();
        }

        echo "Il file e i collegamenti sono stati proposti con successo. In attesa di approvazione.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento della proposta: " . $e->getMessage();
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

/**
 * Recupera le entità in base alla tabella.
 *
 * @param string $tabella Nome della tabella.
 * @return array Lista delle entità.
 */
function getEntitaOptions($tabella) {
    require '../Utilities/dbconnect.php'; // Connessione al database con PDO

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
            return [];
    }

    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carica Media</title>
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

        <h3 class="mt-4">Collega Media a Entità</h3>
        <div id="entita-container">
            <div class="entita-row mb-3">
                <label for="entita_tipo_1" class="form-label">Tipo di Entità</label>
                <select class="form-control" id="entita_tipo_1" name="entita_collegate[0][tabella]" onchange="aggiornaEntitaDropdown(1)" required>
                    <option value="">Seleziona un tipo</option>
                    <option value="modello">Modello</option>
                    <option value="veicolo">Veicolo</option>
                    <option value="azienda_operatrice">Azienda Operatrice</option>
                    <option value="azienda_costruttrice">Azienda Costruttrice</option>
                </select>
                <label for="entita_id_1" class="form-label mt-2">Entità</label>
                <select class="form-control" id="entita_id_1" name="entita_collegate[0][id]" required>
                    <option value="">Seleziona un'entità</option>
                </select>
            </div>
        </div>
        <button type="button" class="btn btn-secondary mb-3" onclick="aggiungiEntita()">Aggiungi Collegamento</button>

        <button type="submit" class="btn btn-primary">Carica Media</button>
        <a href="../index.html" class="btn btn-secondary">Annulla</a>
    </form>

    <script>
        let entitaCounter = 1;

        function aggiungiEntita() {
            entitaCounter++;
            const container = document.getElementById('entita-container');
            const newRow = document.createElement('div');
            newRow.className = 'entita-row mb-3';
            newRow.innerHTML = `
                <label for="entita_tipo_${entitaCounter}" class="form-label">Tipo di Entità</label>
                <select class="form-control" id="entita_tipo_${entitaCounter}" name="entita_collegate[${entitaCounter}][tabella]" onchange="aggiornaEntitaDropdown(${entitaCounter})" required>
                    <option value="">Seleziona un tipo</option>
                    <option value="modello">Modello</option>
                    <option value="veicolo">Veicolo</option>
                    <option value="azienda_operatrice">Azienda Operatrice</option>
                    <option value="azienda_costruttrice">Azienda Costruttrice</option>
                </select>
                <label for="entita_id_${entitaCounter}" class="form-label mt-2">Entità</label>
                <select class="form-control" id="entita_id_${entitaCounter}" name="entita_collegate[${entitaCounter}][id]" required>
                    <option value="">Seleziona un'entità</option>
                </select>
            `;
            container.appendChild(newRow);
        }
    </script>
</body>
</html>
