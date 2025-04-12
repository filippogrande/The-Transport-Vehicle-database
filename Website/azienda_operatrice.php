<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

include 'header.html'; // Include l'header

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

    // Rimuovi spazi e normalizza i dati vuoti
    foreach ($azienda as $key => $value) {
        $azienda[$key] = isset($value) && !empty(trim($value)) ? trim($value) : null;
    }
} catch (PDOException $e) {
    die("Errore nel recupero dei dettagli dell'azienda operatrice: " . $e->getMessage());
}

try {
    // Recupera i dati di stato_modello_azienda per l'azienda selezionata
    $query_stato = "
        SELECT sma.*, m.nome AS nome_modello
        FROM stato_modello_azienda sma
        INNER JOIN modello m ON sma.id_modello = m.id_modello
        WHERE sma.id_azienda = :id_azienda_operatrice
        ORDER BY m.nome ASC
    ";
    $stmt_stato = $pdo->prepare($query_stato);
    $stmt_stato->bindParam(':id_azienda_operatrice', $id_azienda_operatrice, PDO::PARAM_INT);
    $stmt_stato->execute();
    $stati_modello = $stmt_stato->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei dati di stato modello azienda: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h1 class="mb-3"><?php echo htmlspecialchars($azienda['nome_azienda']); ?></h1>
    <div class="row">
        <div class="col-md-4">
            <?php if (!empty($azienda['foto_logo'])): ?>
                <img src="<?php echo htmlspecialchars($azienda['foto_logo']); ?>" alt="Logo di <?php echo htmlspecialchars($azienda['nome_azienda']); ?>" class="img-fluid">
            <?php else: ?>
                <img src="https://via.placeholder.com/150" alt="Logo non disponibile" class="img-fluid">
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <p><strong>Nome Precedente:</strong> <?php echo htmlspecialchars($azienda['nome_precedente'] ?? 'Non disponibile'); ?></p>
            <p><strong>Sede Legale:</strong> <?php echo htmlspecialchars($azienda['sede_legale'] ?? 'Non disponibile'); ?></p>
            <p><strong>Città:</strong> <?php echo htmlspecialchars($azienda['citta'] ?? 'Non disponibile'); ?></p>
            <p><strong>Paese:</strong> <?php echo htmlspecialchars($azienda['paese'] ?? 'Non disponibile'); ?></p>
            <p><strong>Numero di Telefono:</strong> <?php echo htmlspecialchars($azienda['numero_telefono'] ?? 'Non disponibile'); ?></p>
            <p><strong>Email:</strong> 
                <?php if (!empty($azienda['email'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars($azienda['email']); ?>"><?php echo htmlspecialchars($azienda['email']); ?></a>
                <?php else: ?>
                    Non disponibile
                <?php endif; ?>
            </p>
            <p><strong>Data Inizio Attività:</strong> <?php echo htmlspecialchars($azienda['data_inizio_attivita'] ?? 'Non disponibile'); ?></p>
            <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($azienda['descrizione'] ?? 'Non disponibile')); ?></p>
            <p><strong>Stato:</strong> <?php echo htmlspecialchars($azienda['stato_azienda'] ?? 'Non disponibile'); ?></p>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        <a href="/aziende_operatrici.php" class="btn btn-secondary">Torna alla pagina Aziende Operatrici</a>
        <a href="/modifiche/modifica_azienda_operatrice.php?id=<?php echo urlencode($id_azienda_operatrice); ?>" 
           class="btn btn-warning d-flex align-items-center">
            <i class="fas fa-pencil-alt me-2"></i> Modifica
        </a>
    </div>

    <div class="mt-4">
        <a href="/Aggiunte/crea_stato_modello_azienda.php?id_azienda=<?= urlencode($id_azienda_operatrice) ?>" class="btn btn-success">
            Crea Stato Modello Azienda
        </a>
    </div>

    <h2 class="mt-5">Stato Modello Azienda</h2>
    <?php if (!empty($stati_modello)): ?>
        <table class="table table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Modello</th>
                    <th>Totale</th>
                    <th>Attivi</th>
                    <th>Abbandonati</th>
                    <th>Demoliti</th>
                    <th>Museo</th>
                    <th>Ceduti</th>
                    <th>Descrizione</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stati_modello as $stato): ?>
                    <tr>
                        <td><?= htmlspecialchars($stato['nome_modello']) ?></td>
                        <td><?= htmlspecialchars($stato['totale']) ?></td>
                        <td><?= htmlspecialchars($stato['attivi']) ?></td>
                        <td><?= htmlspecialchars($stato['abbandonati']) ?></td>
                        <td><?= htmlspecialchars($stato['demoliti']) ?></td>
                        <td><?= htmlspecialchars($stato['museo']) ?></td>
                        <td><?= htmlspecialchars($stato['ceduti']) ?></td>
                        <td><?= nl2br(htmlspecialchars($stato['descrizione'] ?? 'N/A')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Non ci sono dati di stato modello azienda per questa azienda.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include il footer ?>
