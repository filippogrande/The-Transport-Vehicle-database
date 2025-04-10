<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

include 'header.html'; // Include l'header

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
} catch (PDOException $e) {
    die("Errore nel recupero dei dettagli del veicolo: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h1 class="mb-3">Dettagli Veicolo</h1>
    <div class="row">
        <div class="col-md-6">
            <p><strong>ID Veicolo:</strong> <?php echo htmlspecialchars($veicolo['id_veicolo']); ?></p>
            <p><strong>Modello:</strong> <?php echo htmlspecialchars($veicolo['nome_modello']); ?></p>
            <p><strong>Anno di Produzione:</strong> <?php echo htmlspecialchars($veicolo['anno_produzione'] ?? 'Non disponibile'); ?></p>
            <p><strong>Numero Targa:</strong> <?php echo htmlspecialchars($veicolo['numero_targa'] ?? 'Non disponibile'); ?></p>
            <p><strong>Stato:</strong> <?php echo htmlspecialchars($veicolo['stato_veicolo'] ?? 'Non disponibile'); ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($veicolo['descrizione'] ?? 'Non disponibile')); ?></p>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        <a href="/veicoli.php" class="btn btn-secondary">Torna alla pagina Veicoli</a>
        <a href="/modifiche/modifica_veicolo.php?id=<?php echo urlencode($veicolo['id_veicolo']); ?>" 
           class="btn btn-warning d-flex align-items-center">
            <i class="fas fa-pencil-alt me-2"></i> Modifica
        </a>
    </div>
</div>

<?php include 'footer.php'; // Include il footer ?>
