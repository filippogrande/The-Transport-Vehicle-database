<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

$id_azienda_operatrice = $_GET['id'] ?? null;

if (!$id_azienda_operatrice) {
    die("Errore: ID dell'azienda operatrice non fornito.");
}

try {
    // Recupera i dettagli dell'azienda operatrice
    $query = "SELECT * FROM azienda_operatrice WHERE id_azienda_operatrice = :id_azienda_operatrice";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_azienda_operatrice', $id_azienda_operatrice, PDO::PARAM_INT);
    $stmt->execute();
    $azienda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$azienda) {
        die("Errore: Azienda operatrice non trovata.");
    }

    // Recupera i paesi dalla tabella `nazione`
    $query_nazioni = "SELECT nome FROM nazione ORDER BY nome ASC";
    $stmt_nazioni = $pdo->query($query_nazioni);
    $nazioni = $stmt_nazioni->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $nome_azienda = trim($_POST['nome_azienda']);
    $nome_precedente = trim($_POST['nome_precedente'] ?? null);
    $sede_legale = trim($_POST['sede_legale'] ?? null);
    $citta = trim($_POST['citta'] ?? null);
    $paese = trim($_POST['paese'] ?? null);
    $numero_telefono = trim($_POST['numero_telefono'] ?? null);
    $email = trim($_POST['email'] ?? null);
    $data_inizio_attivita = trim($_POST['data_inizio_attivita'] ?? null);
    $descrizione = trim($_POST['descrizione'] ?? null);
    $stato_azienda = trim($_POST['stato_azienda'] ?? null);

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
        $foto_logo_url = $azienda['foto_logo'] ?? null; // Mantieni l'immagine attuale se non viene caricata una nuova
    }

    // ID gruppo modifica per tracciabilità
    $id_gruppo_modifica = rand(1000, 9999);

    // Inserisci i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, id_entita, campo_modificato, valore_nuovo, valore_vecchio, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'azienda_operatrice', :id_entita, :campo_modificato, :valore_nuovo, :valore_vecchio, 'In attesa', 'admin')";
        $stmt = $pdo->prepare($query);

        $campi = [
            'nome_azienda' => [$nome_azienda, $azienda['nome_azienda']],
            'nome_precedente' => [$nome_precedente, $azienda['nome_precedente']],
            'sede_legale' => [$sede_legale, $azienda['sede_legale']],
            'citta' => [$citta, $azienda['citta']],
            'paese' => [$paese, $azienda['paese']],
            'numero_telefono' => [$numero_telefono, $azienda['numero_telefono']],
            'email' => [$email, $azienda['email']],
            'data_inizio_attivita' => [$data_inizio_attivita, $azienda['data_inizio_attivita']],
            'descrizione' => [$descrizione, $azienda['descrizione']],
            'stato_azienda' => [$stato_azienda, $azienda['stato_azienda']],
            'foto_logo' => [$foto_logo_url, $azienda['foto_logo']],
        ];

        foreach ($campi as $campo => [$valore_nuovo, $valore_vecchio]) {
            if ($valore_nuovo !== $valore_vecchio) {
                $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
                $stmt->bindParam(':id_entita', $id_azienda_operatrice, PDO::PARAM_INT);
                $stmt->bindParam(':campo_modificato', $campo);
                $stmt->bindParam(':valore_nuovo', $valore_nuovo);
                $stmt->bindParam(':valore_vecchio', $valore_vecchio);
                $stmt->execute();
            }
        }

        echo "Le modifiche all'azienda operatrice sono state proposte con successo. In attesa di approvazione.";
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
    <title>Modifica Azienda Operatrice</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Modifica Azienda Operatrice</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nome_azienda" class="form-label">Nome dell'Azienda</label>
            <input type="text" class="form-control" id="nome_azienda" name="nome_azienda" value="<?= htmlspecialchars($azienda['nome_azienda'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="nome_precedente" class="form-label">Nome Precedente</label>
            <input type="text" class="form-control" id="nome_precedente" name="nome_precedente" value="<?= htmlspecialchars($azienda['nome_precedente'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="sede_legale" class="form-label">Sede Legale</label>
            <input type="text" class="form-control" id="sede_legale" name="sede_legale" value="<?= htmlspecialchars($azienda['sede_legale'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="citta" class="form-label">Città</label>
            <input type="text" class="form-control" id="citta" name="citta" value="<?= htmlspecialchars($azienda['citta'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="paese" class="form-label">Paese</label>
            <select class="form-control" id="paese" name="paese">
                <option value="">Seleziona un paese</option>
                <?php foreach ($nazioni as $nazione): ?>
                    <option value="<?= htmlspecialchars($nazione['nome']) ?>" <?= ($azienda['paese'] ?? '') === $nazione['nome'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nazione['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="numero_telefono" class="form-label">Numero di Telefono</label>
            <input type="text" class="form-control" id="numero_telefono" name="numero_telefono" value="<?= htmlspecialchars($azienda['numero_telefono'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($azienda['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="data_inizio_attivita" class="form-label">Data Inizio Attività</label>
            <input type="date" class="form-control" id="data_inizio_attivita" name="data_inizio_attivita" value="<?= htmlspecialchars($azienda['data_inizio_attivita'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="5"><?= htmlspecialchars($azienda['descrizione'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="foto_logo" class="form-label">Carica Nuovo Logo</label>
            <input type="file" class="form-control" id="foto_logo" name="foto_logo" accept="image/*">
            <?php if (!empty($azienda['foto_logo'])): ?>
                <p>Logo attuale: <img src="<?= htmlspecialchars($azienda['foto_logo']) ?>" alt="Logo" style="max-width: 100px; max-height: 50px;"></p>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="stato_azienda" class="form-label">Stato dell'Azienda</label>
            <select class="form-control" id="stato_azienda" name="stato_azienda">
                <option value="Attiva" <?= ($azienda['stato_azienda'] ?? '') === 'Attiva' ? 'selected' : '' ?>>Attiva</option>
                <option value="Chiusa" <?= ($azienda['stato_azienda'] ?? '') === 'Chiusa' ? 'selected' : '' ?>>Chiusa</option>
                <option value="Fallita" <?= ($azienda['stato_azienda'] ?? '') === 'Fallita' ? 'selected' : '' ?>>Fallita</option>
                <option value="Acquisita" <?= ($azienda['stato_azienda'] ?? '') === 'Acquisita' ? 'selected' : '' ?>>Acquisita</option>
                <option value="Rinominata" <?= ($azienda['stato_azienda'] ?? '') === 'Rinominata' ? 'selected' : '' ?>>Rinominata</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Proponi Modifiche</button>
        <a href="../azienda_operatrice.php?id=<?= urlencode($id_azienda_operatrice) ?>" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
