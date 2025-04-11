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

try {
    // Recupera i dettagli delle aziende che hanno posseduto il veicolo
    $query_possessi = "
        SELECT p.data_inizio_possesso, p.data_fine_possesso, p.stato_veicolo_azienda, 
               a.nome_azienda, a.id_azienda_operatrice
        FROM possesso_veicolo p
        INNER JOIN azienda_operatrice a ON p.id_azienda_operatrice = a.id_azienda_operatrice
        WHERE p.id_veicolo = :id_veicolo
        ORDER BY p.data_inizio_possesso ASC
    ";
    $stmt_possessi = $pdo->prepare($query_possessi);
    $stmt_possessi->bindParam(':id_veicolo', $id_veicolo, PDO::PARAM_INT);
    $stmt_possessi->execute();
    $possessi = $stmt_possessi->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei dati di possesso: " . $e->getMessage());
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
        <a href="/Aggiunte/crea_azienda_proprietaria.php?id_veicolo=<?php echo urlencode($veicolo['id_veicolo']); ?>" 
           class="btn btn-success d-flex align-items-center">
            <i class="fas fa-plus me-2"></i> Crea Azienda Proprietaria
        </a>
    </div>

    <h2 class="mt-5">Storico Possessori</h2>
    <?php if (!empty($possessi)): ?>
        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Azienda</th>
                    <th>Data Inizio Possesso</th>
                    <th>Data Fine Possesso</th>
                    <th>Stato del Veicolo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($possessi as $possesso): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($possesso['nome_azienda']); ?></td>
                        <td><?php echo htmlspecialchars($possesso['data_inizio_possesso'] ?? 'Non disponibile'); ?></td>
                        <td><?php echo htmlspecialchars($possesso['data_fine_possesso'] ?? 'In corso'); ?></td>
                        <td><?php echo htmlspecialchars($possesso['stato_veicolo_azienda'] ?? 'Non disponibile'); ?></td>
                        <td>
                            <a href="/modifiche/modifica_possessore.php?id_veicolo=<?php echo urlencode($id_veicolo); ?>&id_azienda=<?php echo urlencode($possesso['id_azienda_operatrice']); ?>" 
                               class="btn btn-warning btn-sm">Modifica</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Non ci sono dati di possesso disponibili per questo veicolo.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include il footer ?>
