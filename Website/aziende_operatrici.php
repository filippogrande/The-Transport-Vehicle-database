<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

include 'header.html'; // Include l'header

try {
    // Recupera tutte le aziende operatrici dal database
    $query = "SELECT id_azienda_operatrice, nome_azienda, città, paese, stato_azienda, foto_logo FROM azienda_operatrice ORDER BY nome_azienda ASC";
    $stmt = $pdo->query($query);
    $aziende = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero delle aziende operatrici: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h1 class="mb-3">Elenco Aziende Operatrici</h1>
    <div class="mb-3 d-flex justify-content-end">
        <a href="/Aggiunte/crea_azienda_operatrice.php" class="btn btn-primary">Crea Nuova Azienda</a>
    </div>
    <?php if (!empty($aziende)): ?>
        <div class="row">
            <?php foreach ($aziende as $azienda): ?>
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <!-- Logo sopra -->
                        <?php if (!empty($azienda['foto_logo'])): ?>
                            <img src="<?php echo htmlspecialchars($azienda['foto_logo']); ?>" class="card-img-top" alt="Logo di <?php echo htmlspecialchars($azienda['nome_azienda']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/150" class="card-img-top" alt="Logo non disponibile">
                        <?php endif; ?>
                        <!-- Dettagli sotto -->
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="/azienda_operatrice.php?id=<?php echo urlencode($azienda['id_azienda_operatrice']); ?>">
                                    <?php echo htmlspecialchars($azienda['nome_azienda']); ?>
                                </a>
                            </h5>
                            <p class="card-text"><strong>Città:</strong> <?php echo htmlspecialchars($azienda['città'] ?? 'N/A'); ?></p>
                            <p class="card-text"><strong>Paese:</strong> <?php echo htmlspecialchars($azienda['paese'] ?? 'N/A'); ?></p>
                            <p class="card-text"><strong>Stato:</strong> <?php echo htmlspecialchars($azienda['stato_azienda'] ?? 'N/A'); ?></p>
                            <a href="/azienda_operatrice.php?id=<?php echo urlencode($azienda['id_azienda_operatrice']); ?>" class="btn btn-info btn-sm">Visualizza Dettagli</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Non ci sono aziende operatrici disponibili.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include il footer ?>
