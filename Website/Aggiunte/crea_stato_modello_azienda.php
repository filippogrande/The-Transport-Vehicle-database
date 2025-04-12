<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

// Recupera le aziende operatrici e i modelli disponibili
try {
    $query_aziende = "SELECT id_azienda_operatrice, nome_azienda FROM azienda_operatrice ORDER BY nome_azienda ASC";
    $stmt_aziende = $pdo->query($query_aziende);
    $aziende = $stmt_aziende->fetchAll(PDO::FETCH_ASSOC);

    $query_modelli = "SELECT id_modello, nome FROM modello ORDER BY nome ASC";
    $stmt_modelli = $pdo->query($query_modelli);
    $modelli = $stmt_modelli->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $id_azienda = trim($_POST['id_azienda']);
    $id_modello = trim($_POST['id_modello']);
    $totale = trim($_POST['totale'] ?? 0);
    $attivi = trim($_POST['attivi'] ?? 0);
    $abbandonati = trim($_POST['abbandonati'] ?? 0);
    $demoliti = trim($_POST['demoliti'] ?? 0);
    $museo = trim($_POST['museo'] ?? 0);
    $ceduti = trim($_POST['ceduti'] ?? 0);
    $descrizione = trim($_POST['descrizione'] ?? null);

    // Verifica che azienda e modello siano stati selezionati
    if (empty($id_azienda) || empty($id_modello)) {
        die("Errore: È necessario selezionare un'azienda e un modello.");
    }

    // ID gruppo modifica per tracciabilità
    $id_gruppo_modifica = rand(1000, 9999);

    // Inserisci i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'stato_modello_azienda', :campo_modificato, :valore_nuovo, 'In attesa', 'admin')";
        $stmt = $pdo->prepare($query);

        $campi = [
            'id_azienda' => $id_azienda,
            'id_modello' => $id_modello,
            'totale' => $totale,
            'attivi' => $attivi,
            'abbandonati' => $abbandonati,
            'demoliti' => $demoliti,
            'museo' => $museo,
            'ceduti' => $ceduti,
            'descrizione' => $descrizione,
        ];

        foreach ($campi as $campo => $valore_nuovo) {
            if ($valore_nuovo !== null) {
                $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
                $stmt->bindParam(':campo_modificato', $campo);
                $stmt->bindParam(':valore_nuovo', $valore_nuovo);
                $stmt->execute();
            }
        }

        echo "Le informazioni sullo stato del modello per l'azienda sono state proposte con successo. In attesa di approvazione.";
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
    <title>Crea Stato Modello Azienda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Stato Modello Azienda</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="id_azienda" class="form-label">Azienda Operatrice</label>
            <select class="form-control" id="id_azienda" name="id_azienda" required>
                <option value="">Seleziona un'azienda</option>
                <?php foreach ($aziende as $azienda): ?>
                    <option value="<?= htmlspecialchars($azienda['id_azienda_operatrice']) ?>">
                        <?= htmlspecialchars($azienda['nome_azienda']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="id_modello" class="form-label">Modello</label>
            <select class="form-control" id="id_modello" name="id_modello" required>
                <option value="">Seleziona un modello</option>
                <?php foreach ($modelli as $modello): ?>
                    <option value="<?= htmlspecialchars($modello['id_modello']) ?>">
                        <?= htmlspecialchars($modello['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="totale" class="form-label">Totale</label>
            <input type="number" class="form-control" id="totale" name="totale" min="0" value="0">
        </div>
        <div class="mb-3">
            <label for="attivi" class="form-label">Attivi</label>
            <input type="number" class="form-control" id="attivi" name="attivi" min="0" value="0">
        </div>
        <div class="mb-3">
            <label for="abbandonati" class="form-label">Abbandonati</label>
            <input type="number" class="form-control" id="abbandonati" name="abbandonati" min="0" value="0">
        </div>
        <div class="mb-3">
            <label for="demoliti" class="form-label">Demoliti</label>
            <input type="number" class="form-control" id="demoliti" name="demoliti" min="0" value="0">
        </div>
        <div class="mb-3">
            <label for="museo" class="form-label">Museo</label>
            <input type="number" class="form-control" id="museo" name="museo" min="0" value="0">
        </div>
        <div class="mb-3">
            <label for="ceduti" class="form-label">Ceduti</label>
            <input type="number" class="form-control" id="ceduti" name="ceduti" min="0" value="0">
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="5"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Proponi Stato Modello Azienda</button>
        <a href="../index.html" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
