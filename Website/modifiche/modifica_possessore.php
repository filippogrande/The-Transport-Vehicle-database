<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

$id_veicolo = $_GET['id_veicolo'] ?? null;
$id_azienda_operatrice = $_GET['id_azienda'] ?? null;

if (!$id_veicolo || !$id_azienda_operatrice) {
    die("Errore: ID del veicolo o dell'azienda operatrice non fornito.");
}

try {
    // Recupera i dettagli del possesso
    $query = "
        SELECT p.*, a.nome_azienda 
        FROM possesso_veicolo p
        INNER JOIN azienda_operatrice a ON p.id_azienda_operatrice = a.id_azienda_operatrice
        WHERE p.id_veicolo = :id_veicolo AND p.id_azienda_operatrice = :id_azienda_operatrice
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_veicolo', $id_veicolo, PDO::PARAM_INT);
    $stmt->bindParam(':id_azienda_operatrice', $id_azienda_operatrice, PDO::PARAM_INT);
    $stmt->execute();
    $possesso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$possesso) {
        die("Errore: Dettagli del possesso non trovati.");
    }
} catch (PDOException $e) {
    die("Errore nel recupero dei dati: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['elimina_possesso'])) {
        // Richiesta di eliminazione del possesso
        try {
            $id_gruppo_modifica = rand(1000, 9999);

            $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, id_entita, campo_modificato, valore_nuovo, stato, autore) 
                      VALUES (:id_gruppo_modifica, 'possesso_veicolo', :id_entita, 'eliminazione', 'richiesta', 'In attesa', 'admin')";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
            $stmt->bindParam(':id_entita', $id_veicolo, PDO::PARAM_INT);
            $stmt->execute();

            echo "La richiesta di eliminazione del possesso è stata inviata con successo. In attesa di approvazione.";
        } catch (PDOException $e) {
            echo "Errore nell'inserimento della richiesta di eliminazione: " . $e->getMessage();
        }
    } else {
        // Ottieni i dati inviati dal form
        $data_inizio_possesso = trim($_POST['data_inizio_possesso'] ?? null);
        $data_fine_possesso = trim($_POST['data_fine_possesso'] ?? null);
        $stato_veicolo_azienda = trim($_POST['stato_veicolo_azienda'] ?? null);

        // ID gruppo modifica per tracciabilità
        $id_gruppo_modifica = rand(1000, 9999);

        // Inserisci i dati nella tabella `modifiche_in_sospeso`
        try {
            $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, id_entita, campo_modificato, valore_nuovo, valore_vecchio, stato, autore) 
                      VALUES (:id_gruppo_modifica, 'possesso_veicolo', :id_entita, :campo_modificato, :valore_nuovo, :valore_vecchio, 'In attesa', 'admin')";
            $stmt = $pdo->prepare($query);

            $campi = [
                'data_inizio_possesso' => [$data_inizio_possesso, $possesso['data_inizio_possesso']],
                'data_fine_possesso' => [$data_fine_possesso, $possesso['data_fine_possesso']],
                'stato_veicolo_azienda' => [$stato_veicolo_azienda, $possesso['stato_veicolo_azienda']],
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

            echo "Le modifiche al possesso sono state proposte con successo. In attesa di approvazione.";
        } catch (PDOException $e) {
            echo "Errore nell'inserimento della modifica: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Possesso Veicolo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Modifica Possesso Veicolo</h1>
    <p><strong>Azienda:</strong> <?php echo htmlspecialchars($possesso['nome_azienda']); ?></p>

    <form method="POST">
        <div class="mb-3">
            <label for="data_inizio_possesso" class="form-label">Data Inizio Possesso</label>
            <input type="date" class="form-control" id="data_inizio_possesso" name="data_inizio_possesso" value="<?php echo htmlspecialchars($possesso['data_inizio_possesso'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="data_fine_possesso" class="form-label">Data Fine Possesso</label>
            <input type="date" class="form-control" id="data_fine_possesso" name="data_fine_possesso" value="<?php echo htmlspecialchars($possesso['data_fine_possesso'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="stato_veicolo_azienda" class="form-label">Stato del Veicolo durante il Possesso</label>
            <select class="form-control" id="stato_veicolo_azienda" name="stato_veicolo_azienda">
                <option value="Attivo" <?php echo ($possesso['stato_veicolo_azienda'] === 'Attivo') ? 'selected' : ''; ?>>Attivo</option>
                <option value="In manutenzione" <?php echo ($possesso['stato_veicolo_azienda'] === 'In manutenzione') ? 'selected' : ''; ?>>In manutenzione</option>
                <option value="Fuori servizio" <?php echo ($possesso['stato_veicolo_azienda'] === 'Fuori servizio') ? 'selected' : ''; ?>>Fuori servizio</option>
                <option value="Rottamato" <?php echo ($possesso['stato_veicolo_azienda'] === 'Rottamato') ? 'selected' : ''; ?>>Rottamato</option>
            </select>
        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Proponi Modifiche</button>
            <button type="submit" name="elimina_possesso" class="btn btn-danger">Richiedi Eliminazione</button>
            <a href="../veicolo.php?id=<?php echo urlencode($id_veicolo); ?>" class="btn btn-secondary">Annulla</a>
        </div>
    </form>
</body>
</html>
