<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

$id_veicolo = $_GET['id_veicolo'] ?? null;

if (!$id_veicolo) {
    die("Errore: ID del veicolo non fornito.");
}

try {
    // Recupera i dettagli del veicolo
    $query_veicolo = "SELECT id_veicolo, numero_targa FROM veicolo WHERE id_veicolo = :id_veicolo";
    $stmt_veicolo = $pdo->prepare($query_veicolo);
    $stmt_veicolo->bindParam(':id_veicolo', $id_veicolo, PDO::PARAM_INT);
    $stmt_veicolo->execute();
    $veicolo = $stmt_veicolo->fetch(PDO::FETCH_ASSOC);

    if (!$veicolo) {
        die("Errore: Veicolo non trovato.");
    }

    // Recupera le aziende operatrici disponibili
    $query_aziende = "SELECT id_azienda_operatrice, nome_azienda FROM azienda_operatrice ORDER BY nome_azienda ASC";
    $stmt_aziende = $pdo->query($query_aziende);
    $aziende = $stmt_aziende->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $id_azienda_operatrice = trim($_POST['id_azienda_operatrice']);
    $data_inizio_possesso = trim($_POST['data_inizio_possesso'] ?? null);
    $data_fine_possesso = trim($_POST['data_fine_possesso'] ?? null);
    $stato_veicolo_azienda = trim($_POST['stato_veicolo_azienda'] ?? null);

    // Verifica che l'azienda operatrice sia stata selezionata
    if (empty($id_azienda_operatrice)) {
        die("Errore: È necessario selezionare un'azienda operatrice.");
    }

    // Inserisci i dati nella tabella `possesso_veicolo`
    try {
        $query = "
            INSERT INTO possesso_veicolo (id_veicolo, id_azienda_operatrice, data_inizio_possesso, data_fine_possesso, stato_veicolo_azienda)
            VALUES (:id_veicolo, :id_azienda_operatrice, :data_inizio_possesso, :data_fine_possesso, :stato_veicolo_azienda)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_veicolo', $id_veicolo, PDO::PARAM_INT);
        $stmt->bindParam(':id_azienda_operatrice', $id_azienda_operatrice, PDO::PARAM_INT);
        $stmt->bindParam(':data_inizio_possesso', $data_inizio_possesso);
        $stmt->bindParam(':data_fine_possesso', $data_fine_possesso);
        $stmt->bindParam(':stato_veicolo_azienda', $stato_veicolo_azienda);

        $stmt->execute();

        echo "L'azienda proprietaria è stata aggiunta con successo.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento dell'azienda proprietaria: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Azienda Proprietaria</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Azienda Proprietaria per Veicolo "<?php echo htmlspecialchars($veicolo['numero_targa']); ?>"</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="id_azienda_operatrice" class="form-label">Azienda Operatrice</label>
            <select class="form-control" id="id_azienda_operatrice" name="id_azienda_operatrice" required>
                <option value="">Seleziona un'azienda</option>
                <?php foreach ($aziende as $azienda): ?>
                    <option value="<?php echo htmlspecialchars($azienda['id_azienda_operatrice']); ?>">
                        <?php echo htmlspecialchars($azienda['nome_azienda']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="data_inizio_possesso" class="form-label">Data Inizio Possesso</label>
            <input type="date" class="form-control" id="data_inizio_possesso" name="data_inizio_possesso">
        </div>
        <div class="mb-3">
            <label for="data_fine_possesso" class="form-label">Data Fine Possesso</label>
            <input type="date" class="form-control" id="data_fine_possesso" name="data_fine_possesso">
        </div>
        <div class="mb-3">
            <label for="stato_veicolo_azienda" class="form-label">Stato del Veicolo durante il Possesso</label>
            <select class="form-control" id="stato_veicolo_azienda" name="stato_veicolo_azienda">
                <option value="">Seleziona uno stato</option>
                <option value="Attivo">Attivo</option>
                <option value="In manutenzione">In manutenzione</option>
                <option value="Fuori servizio">Fuori servizio</option>
                <option value="Rottamato">Rottamato</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Aggiungi Azienda Proprietaria</button>
        <a href="../veicolo.php?id=<?php echo urlencode($id_veicolo); ?>" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
