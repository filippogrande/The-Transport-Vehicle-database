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
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modelli as $modello): ?>
                    <tr>
                        <td>
                            <a href="/modello.php?id=<?php echo urlencode($modello['id_modello']); ?>">
                                <?php echo htmlspecialchars($modello['nome']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($modello['tipo'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="/modello.php?id=<?php echo urlencode($modello['id_modello']); ?>" class="btn btn-info btn-sm">Visualizza</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Non ci sono modelli disponibili.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include il footer ?>
