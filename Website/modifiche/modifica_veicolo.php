<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

$id_veicolo = $_GET['id'] ?? null;

if (!$id_veicolo) {
    die("Errore: ID del veicolo non fornito.");
}

try {
    // Recupera i dettagli del veicolo
    $query = "
        SELECT v.*, m.nome AS nome_modello 
        FROM veicolo v
        INNER JOIN modello m ON v.id_modello = m.id_modello
        WHERE v.id_veicolo = :id_veicolo
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_veicolo', $id_veicolo, PDO::PARAM_INT);
    $stmt->execute();
    $veicolo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$veicolo) {
        die("Errore: Veicolo non trovato.");
    }

    // Recupera i modelli disponibili
    $query_modelli = "SELECT id_modello, nome FROM modello ORDER BY nome ASC";
    $stmt_modelli = $pdo->query($query_modelli);
    $modelli = $stmt_modelli->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $id_modello = trim($_POST['id_modello']);
    $anno_produzione = trim($_POST['anno_produzione'] ?? null);
    $numero_targa = trim($_POST['numero_targa'] ?? null);
    $descrizione = trim($_POST['descrizione'] ?? null);
    $stato_veicolo = trim($_POST['stato_veicolo'] ?? null);

    // ID gruppo modifica per tracciabilità
    $id_gruppo_modifica = rand(1000, 9999);

    // Inserisci i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, id_entita, campo_modificato, valore_nuovo, valore_vecchio, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'veicolo', :id_entita, :campo_modificato, :valore_nuovo, :valore_vecchio, 'In attesa', 'admin')";
        $stmt = $pdo->prepare($query);

        $campi = [
            'id_modello' => [$id_modello, $veicolo['id_modello']],
            'anno_produzione' => [$anno_produzione, $veicolo['anno_produzione']],
            'numero_targa' => [$numero_targa, $veicolo['numero_targa']],
            'descrizione' => [$descrizione, $veicolo['descrizione']],
            'stato_veicolo' => [$stato_veicolo, $veicolo['stato_veicolo']],
        ];

        foreach ($campi as $campo => [$valore_nuovo, $valore_vecchio]) {
            if ($valore_nuovo !== $valore_vecchio) {
                $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
                $stmt->bindParam(':id_entita', $id_veicolo, PDO::PARAM_INT);
                $stmt->bindParam(':campo_modificato', $campo);
                $stmt->bindParam(':valore_nuovo', $valore_nuovo);
                $stmt->bindParam(':valore_vecchio', $valore_vecchio);
                $stmt->execute();
            }
        }

        echo "Le modifiche al veicolo sono state proposte con successo. In attesa di approvazione.";
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
    <title>Modifica Veicolo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Modifica Veicolo</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="id_modello" class="form-label">Modello</label>
            <select class="form-control" id="id_modello" name="id_modello" required>
                <?php foreach ($modelli as $modello): ?>
                    <option value="<?= htmlspecialchars($modello['id_modello']) ?>" <?= $veicolo['id_modello'] == $modello['id_modello'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($modello['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="anno_produzione" class="form-label">Anno di Produzione</label>
            <input type="number" class="form-control" id="anno_produzione" name="anno_produzione" value="<?= htmlspecialchars($veicolo['anno_produzione'] ?? '') ?>" min="1900" max="2100">
        </div>
        <div class="mb-3">
            <label for="numero_targa" class="form-label">Numero Targa</label>
            <input type="text" class="form-control" id="numero_targa" name="numero_targa" value="<?= htmlspecialchars($veicolo['numero_targa'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="5"><?= htmlspecialchars($veicolo['descrizione'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="stato_veicolo" class="form-label">Stato del Veicolo</label>
            <select class="form-control" id="stato_veicolo" name="stato_veicolo">
                <option value="Attivo" <?= $veicolo['stato_veicolo'] === 'Attivo' ? 'selected' : '' ?>>Attivo</option>
                <option value="Abbandonato" <?= $veicolo['stato_veicolo'] === 'Abbandonato' ? 'selected' : '' ?>>Abbandonato</option>
                <option value="Demolito" <?= $veicolo['stato_veicolo'] === 'Demolito' ? 'selected' : '' ?>>Demolito</option>
                <option value="Museo" <?= $veicolo['stato_veicolo'] === 'Museo' ? 'selected' : '' ?>>Museo</option>
                <option value="Ceduto" <?= $veicolo['stato_veicolo'] === 'Ceduto' ? 'selected' : '' ?>>Ceduto</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Proponi Modifiche</button>
        <a href="../veicolo.php?id=<?= urlencode($id_veicolo) ?>" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
