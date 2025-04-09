<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

include 'header.html'; // Include l'header

try {
    // Recupera tutti i modelli dal database
    $query = "SELECT id_modello, nome, tipo FROM modello ORDER BY nome ASC";
    $stmt = $pdo->query($query);
    $modelli = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei modelli: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h1 class="mb-3">Elenco Modelli</h1>
    <div class="mb-3 d-flex justify-content-end">
        <a href="/Aggiunte/crea_modello.php" class="btn btn-primary">Crea Nuovo Modello</a>
    </div>
    <?php if (!empty($modelli)): ?>
        <div class="row">
            <?php foreach ($modelli as $modello): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="row g-0">
                            <!-- Spazio per la foto -->
                            <div class="col-md-4">
                                <img src="https://via.placeholder.com/150" class="img-fluid rounded-start" alt="Foto di <?php echo htmlspecialchars($modello['nome']); ?>">
                            </div>
                            <!-- Dettagli del modello -->
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="/modello.php?id=<?php echo urlencode($modello['id_modello']); ?>">
                                            <?php echo htmlspecialchars($modello['nome']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text"><strong>Tipo:</strong> <?php echo htmlspecialchars($modello['tipo'] ?? 'N/A'); ?></p>
                                    <a href="/modello.php?id=<?php echo urlencode($modello['id_modello']); ?>" class="btn btn-info btn-sm">Visualizza Dettagli</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Non ci sono modelli disponibili.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include il footer ?>
