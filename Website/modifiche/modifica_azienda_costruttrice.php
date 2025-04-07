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

// Recupera l'id dell'azienda dalla query string
$id_azienda = $_GET['id'] ?? null;

if (!$id_azienda) {
    die("Errore: ID dell'azienda non fornito.");
}

try {
    // Recupera i dati dell'azienda dal database
    $query = "SELECT * FROM azienda_costruttrice WHERE id_azienda = :id_azienda";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_azienda', $id_azienda, PDO::PARAM_INT);
    $stmt->execute();
    $azienda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$azienda) {
        die("Errore: Azienda non trovata.");
    }
} catch (PDOException $e) {
    die("Errore nella query: " . $e->getMessage());
}

// Recupera le nazioni dal database
$nazioni = [];
try {
    $stmt = $pdo->query("SELECT nome FROM nazione");
    $nazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero delle nazioni: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $nome = trim($_POST['nome'] ?? '');
    $short_desc = trim($_POST['short_desc'] ?? null);
    $long_desc = trim($_POST['long_desc'] ?? null);
    $fondazione = trim($_POST['fondazione'] ?? null);
    $chiusura = trim($_POST['chiusura'] ?? null);
    $sede = trim($_POST['sede'] ?? null);
    $nazione = trim($_POST['nazione'] ?? null);
    $sito_web = trim($_POST['sito_web'] ?? null);
    $stato = trim($_POST['stato'] ?? null);

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
        $logo_url = $azienda['logo'] ?? null; // Mantieni l'immagine attuale se non viene caricata una nuova
    }

    // ID gruppo modifica per tracciabilità
    $id_gruppo_modifica = rand(1000, 9999);

    // Inseriamo i dati nella tabella `modifiche_in_sospeso` per tutti i campi
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, valore_vecchio, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'azienda_costruttrice', :campo_modificato, :valore_nuovo, :valore_vecchio, 'In attesa', 'admin')";
        $stmt = $pdo->prepare($query);

        $campi = [
            'nome' => [$nome, $azienda['nome'] ?? null],
            'short_desc' => [$short_desc, $azienda['short_desc'] ?? null],
            'long_desc' => [$long_desc, $azienda['long_desc'] ?? null],
            'fondazione' => [$fondazione, $azienda['fondazione'] ?? null],
            'chiusura' => [$chiusura, $azienda['chiusura'] ?? null],
            'sede' => [$sede, $azienda['sede'] ?? null],
            'nazione' => [$nazione, $azienda['nazione'] ?? null],
            'sito_web' => [$sito_web, $azienda['sito_web'] ?? null],
            'stato' => [$stato, $azienda['stato'] ?? null],
            'logo' => [$logo_url, $azienda['logo'] ?? null],
        ];

        foreach ($campi as $campo => [$valore_nuovo, $valore_vecchio]) {
            $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
            $stmt->bindParam(':campo_modificato', $campo);
            $stmt->bindParam(':valore_nuovo', $valore_nuovo);
            $stmt->bindParam(':valore_vecchio', $valore_vecchio);
            $stmt->execute();
        }

        echo "Le modifiche all'azienda sono state proposte con successo. In attesa di approvazione.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento della modifica: " . $e->getMessage();
    }
}

include '../header.html'; // Include l'header
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Azienda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Modifica Azienda</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome dell'Azienda</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($azienda['nome'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="short_desc" class="form-label">Descrizione Breve</label>
            <input type="text" class="form-control" id="short_desc" name="short_desc" value="<?= htmlspecialchars($azienda['short_desc'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="long_desc" class="form-label">Descrizione Lunga</label>
            <textarea class="form-control" id="long_desc" name="long_desc" rows="5" style="resize: vertical;"><?= htmlspecialchars($azienda['long_desc'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="fondazione" class="form-label">Data di Fondazione</label>
            <input type="date" class="form-control" id="fondazione" name="fondazione" value="<?= htmlspecialchars($azienda['fondazione'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="chiusura" class="form-label">Data di Chiusura (se applicabile)</label>
            <input type="date" class="form-control" id="chiusura" name="chiusura" value="<?= htmlspecialchars($azienda['chiusura'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="sede" class="form-label">Sede</label>
            <input type="text" class="form-control" id="sede" name="sede" value="<?= htmlspecialchars($azienda['sede'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="nazione" class="form-label">Nazione</label>
            <select class="form-control" id="nazione" name="nazione">
                <option value="">Seleziona una nazione</option>
                <?php foreach ($nazioni as $n): ?>
                    <option value="<?= htmlspecialchars($n['nome']) ?>" <?= ($azienda['nazione'] ?? '') === $n['nome'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($n['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="sito_web" class="form-label">Sito Web</label>
            <input type="url" class="form-control" id="sito_web" name="sito_web" value="<?= htmlspecialchars($azienda['sito_web'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="stato" class="form-label">Stato</label>
            <select class="form-control" id="stato" name="stato">
                <option value="Attiva" <?= ($azienda['stato'] ?? '') === 'Attiva' ? 'selected' : '' ?>>Attiva</option>
                <option value="Chiusa" <?= ($azienda['stato'] ?? '') === 'Chiusa' ? 'selected' : '' ?>>Chiusa</option>
                <option value="Fallita" <?= ($azienda['stato'] ?? '') === 'Fallita' ? 'selected' : '' ?>>Fallita</option>
                <option value="Acquisita" <?= ($azienda['stato'] ?? '') === 'Acquisita' ? 'selected' : '' ?>>Acquisita</option>
                <option value="Rinominata" <?= ($azienda['stato'] ?? '') === 'Rinominata' ? 'selected' : '' ?>>Rinominata</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="logo" class="form-label">Carica Nuovo Logo</label>
            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
            <?php if (!empty($azienda['logo'])): ?>
                <p>Logo attuale: <img src="<?= htmlspecialchars($azienda['logo']) ?>" alt="Logo" style="max-width: 100px; max-height: 50px;"></p>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Proponi Modifiche</button>
        <a href="../azienda_costruttrice.php?id=<?= urlencode($id_azienda) ?>" class="btn btn-secondary">Torna alla azienda costruttrice</a>
    </form>
</body>
</html>
