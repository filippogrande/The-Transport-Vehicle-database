<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

include 'header.html'; // Include l'header

try {
    // Recupera tutti i veicoli dal database
    $query = "
        SELECT v.id_veicolo, v.anno_produzione, v.numero_targa, v.descrizione, v.stato_veicolo, 
               m.nome AS nome_modello
        FROM veicolo v
        INNER JOIN modello m ON v.id_modello = m.id_modello
        ORDER BY v.id_veicolo ASC
    ";
    $stmt = $pdo->query($query);
    $veicoli = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei veicoli: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h1 class="mb-3">Elenco Veicoli</h1>
    <div class="mb-3 d-flex justify-content-end">
        <a href="/Aggiunte/crea_veicolo.php" class="btn btn-primary">Aggiungi Nuovo Veicolo</a>
    </div>
    <?php if (!empty($veicoli)): ?>
        <div class="row">
            <?php foreach ($veicoli as $veicolo): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ID Veicolo: <?php echo htmlspecialchars($veicolo['id_veicolo']); ?></h5>
                            <p class="card-text"><strong>Modello:</strong> <?php echo htmlspecialchars($veicolo['nome_modello']); ?></p>
                            <p class="card-text"><strong>Anno di Produzione:</strong> <?php echo htmlspecialchars($veicolo['anno_produzione'] ?? 'N/A'); ?></p>
                            <p class="card-text"><strong>Numero Targa:</strong> <?php echo htmlspecialchars($veicolo['numero_targa'] ?? 'N/A'); ?></p>
                            <p class="card-text"><strong>Stato:</strong> <?php echo htmlspecialchars($veicolo['stato_veicolo'] ?? 'N/A'); ?></p>
                            <p class="card-text"><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($veicolo['descrizione'] ?? 'N/A')); ?></p>
                            <a href="/veicolo.php?id=<?php echo urlencode($veicolo['id_veicolo']); ?>" class="btn btn-info btn-sm">Visualizza Dettagli</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Non ci sono veicoli disponibili.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include il footer ?>
