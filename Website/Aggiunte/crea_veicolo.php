<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

// Recupera i modelli disponibili dalla tabella `modello`
try {
    $query_modelli = "SELECT id_modello, nome FROM modello ORDER BY nome ASC";
    $stmt_modelli = $pdo->query($query_modelli);
    $modelli = $stmt_modelli->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei modelli: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $id_modello = trim($_POST['id_modello']);
    $anno_produzione = trim($_POST['anno_produzione'] ?? null);
    $numero_targa = trim($_POST['numero_targa'] ?? null);
    $descrizione = trim($_POST['descrizione'] ?? null);
    $stato_veicolo = trim($_POST['stato_veicolo'] ?? 'Attivo');

    // Verifica che il modello sia stato selezionato
    if (empty($id_modello)) {
        die("Errore: È necessario selezionare un modello.");
    }

    // ID gruppo modifica per raggruppare le modifiche
    $id_gruppo_modifica = rand(1000, 9999);

    // Inserisci i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'veicolo', :campo_modificato, :valore_nuovo, 'In attesa', 'admin')";
        $stmt = $pdo->prepare($query);

        $campi = [
            'id_modello' => $id_modello,
            'anno_produzione' => $anno_produzione,
            'numero_targa' => $numero_targa,
            'descrizione' => $descrizione,
            'stato_veicolo' => $stato_veicolo,
        ];

        foreach ($campi as $campo => $valore_nuovo) {
            if ($valore_nuovo !== null) {
                $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
                $stmt->bindParam(':campo_modificato', $campo);
                $stmt->bindParam(':valore_nuovo', $valore_nuovo);
                $stmt->execute();
            }
        }

        echo "Il veicolo è stato proposto con successo. In attesa di approvazione.";
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
    <title>Crea Veicolo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Nuovo Veicolo</h1>

    <form method="POST">
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
            <label for="anno_produzione" class="form-label">Anno di Produzione</label>
            <input type="number" class="form-control" id="anno_produzione" name="anno_produzione" min="1900" max="2100">
        </div>
        <div class="mb-3">
            <label for="numero_targa" class="form-label">Numero Targa</label>
            <input type="text" class="form-control" id="numero_targa" name="numero_targa">
        </div>
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="5"></textarea>
        </div>
        <div class="mb-3">
            <label for="stato_veicolo" class="form-label">Stato del Veicolo</label>
            <select class="form-control" id="stato_veicolo" name="stato_veicolo">
                <option value="Attivo">Attivo</option>
                <option value="In manutenzione">In manutenzione</option>
                <option value="Fuori servizio">Fuori servizio</option>
                <option value="Rottamato">Rottamato</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Proponi Veicolo</button>
        <a href="../veicoli.php" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
