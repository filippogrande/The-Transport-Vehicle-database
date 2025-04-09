<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

include 'header.html'; // Include l'header

$id_modello = $_GET['id'] ?? null;

if (!$id_modello) {
    die("Errore: ID del modello non fornito.");
}

$query = "SELECT * FROM modello WHERE id_modello = :id_modello";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_modello', $id_modello, PDO::PARAM_INT);
$stmt->execute();
$modello = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$modello) {
    die("Errore: Modello non trovato.");
}
?>

<div class="container">
    <?php if (!empty($modello)): ?>
        <div class="row">
            <!-- Descrizione sulla sinistra -->
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($modello['nome']); ?></h1>
                <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($modello['descrizione'] ?? 'N/A')); ?></p>
            </div>
            <!-- Dettagli sulla destra -->
            <div class="col-md-6">
                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($modello['tipo'] ?? 'N/A'); ?></p>
                <p><strong>Inizio Produzione:</strong> 
                    <?php echo !empty($modello['anno_inizio_produzione']) ? htmlspecialchars(substr($modello['anno_inizio_produzione'], 0, 4)) : 'N/A'; ?>
                </p>
                <p><strong>Fine Produzione:</strong> 
                    <?php echo !empty($modello['anno_fine_produzione']) ? htmlspecialchars(substr($modello['anno_fine_produzione'], 0, 4)) : 'N/A'; ?>
                </p>
                <p><strong>Posti Seduti:</strong> <?php echo htmlspecialchars($modello['posti_seduti'] ?? 'N/A'); ?></p>
                <p><strong>Posti in Piedi:</strong> <?php echo htmlspecialchars($modello['posti_in_piedi'] ?? 'N/A'); ?></p>
                <p><strong>Posti Carrozzine:</strong> <?php echo htmlspecialchars($modello['posti_carrozzine'] ?? 'N/A'); ?></p>
                <p><strong>Dimensioni (Lunghezza x Larghezza x Altezza):</strong> 
                    <?php echo htmlspecialchars($modello['lunghezza'] ?? 'N/A'); ?> m x 
                    <?php echo htmlspecialchars($modello['larghezza'] ?? 'N/A'); ?> m x 
                    <?php echo htmlspecialchars($modello['altezza'] ?? 'N/A'); ?> m
                </p>
                <p><strong>Peso:</strong> 
                    <?php 
                    if (!empty($modello['peso'])) {
                        $peso = (float) $modello['peso'];
                        if ($peso >= 1000) {
                            echo htmlspecialchars(number_format($peso / 1000, 2)) . " t"; // Mostra in tonnellate
                        } else {
                            echo htmlspecialchars(number_format($peso, 2)) . " kg"; // Mostra in chilogrammi
                        }
                    } else {
                        echo "N/A";
                    }
                    ?>
                </p>
                <p><strong>Motorizzazione:</strong> <?php echo htmlspecialchars($modello['motorizzazione'] ?? 'N/A'); ?></p>
                <p><strong>Velocità Massima:</strong> <?php echo htmlspecialchars($modello['velocita_massima'] ?? 'N/A'); ?> km/h</p>
                <p><strong>Totale Veicoli Prodotti:</strong> <?php echo htmlspecialchars($modello['totale_veicoli'] ?? 'N/A'); ?></p>
            </div>
        </div>
    <?php else: ?>
        <p>Modello non trovato.</p>
    <?php endif; ?>
    <div class="mt-4">
        <a href="/modelli.php" class="btn btn-secondary">Torna alla pagina Modelli</a>
    </div>
</div>

<?php include 'footer.php'; // Include il footer ?>
