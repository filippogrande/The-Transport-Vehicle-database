<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

include 'header.html'; // Include l'header

$id_modello = $_GET['id'] ?? null;

if ($id_modello) {
    $query = "SELECT * FROM modello WHERE id_modello = :id_modello";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_modello', $id_modello, PDO::PARAM_INT);
    $stmt->execute();
    $modello = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container">
    <div class="mt-4 d-flex justify-content-end">
        <a href="/Aggiunte/crea_modello.php" class="btn btn-primary">Crea Nuovo Modello</a>
    </div>
    <?php if (!empty($modello)): ?>
        <h1><?php echo htmlspecialchars($modello['nome']); ?></h1>
        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($modello['tipo'] ?? 'N/A'); ?></p>
        <p><strong>Anno Inizio Produzione:</strong> <?php echo htmlspecialchars($modello['anno_inizio_produzione'] ?? 'N/A'); ?></p>
        <p><strong>Anno Fine Produzione:</strong> <?php echo htmlspecialchars($modello['anno_fine_produzione'] ?? 'N/A'); ?></p>
        <p><strong>Posti Seduti:</strong> <?php echo htmlspecialchars($modello['posti_seduti'] ?? 'N/A'); ?></p>
        <p><strong>Posti in Piedi:</strong> <?php echo htmlspecialchars($modello['posti_in_piedi'] ?? 'N/A'); ?></p>
        <p><strong>Posti Carrozzine:</strong> <?php echo htmlspecialchars($modello['posti_carrozzine'] ?? 'N/A'); ?></p>
        <p><strong>Dimensioni (Lunghezza x Larghezza x Altezza):</strong> 
            <?php echo htmlspecialchars($modello['lunghezza'] ?? 'N/A'); ?> m x 
            <?php echo htmlspecialchars($modello['larghezza'] ?? 'N/A'); ?> m x 
            <?php echo htmlspecialchars($modello['altezza'] ?? 'N/A'); ?> m
        </p>
        <p><strong>Peso:</strong> <?php echo htmlspecialchars($modello['peso'] ?? 'N/A'); ?> kg</p>
        <p><strong>Motorizzazione:</strong> <?php echo htmlspecialchars($modello['motorizzazione'] ?? 'N/A'); ?></p>
        <p><strong>Velocità Massima:</strong> <?php echo htmlspecialchars($modello['velocita_massima'] ?? 'N/A'); ?> km/h</p>
        <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($modello['descrizione'] ?? 'N/A')); ?></p>
        <p><strong>Totale Veicoli Prodotti:</strong> <?php echo htmlspecialchars($modello['totale_veicoli'] ?? 'N/A'); ?></p>
    <?php else: ?>
        <p>Modello non trovato.</p>
    <?php endif; ?>
    <div class="mt-4">
        <a href="/modelli.php" class="btn btn-secondary">Torna alla pagina Modelli</a>
    </div>
</div>

<?php include 'footer.php'; // Include il footer ?>
