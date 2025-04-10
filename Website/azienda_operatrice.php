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
} catch (PDOException $e) {
    die("Errore nel recupero dei dettagli dell'azienda operatrice: " . $e->getMessage());
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
            <p><strong>Nome Precedente:</strong> <?php echo htmlspecialchars($azienda['nome_precedente'] ?? 'N/A'); ?></p>
            <p><strong>Sede Legale:</strong> <?php echo htmlspecialchars($azienda['sede_legale'] ?? 'N/A'); ?></p>
            <p><strong>Città:</strong> <?php echo htmlspecialchars($azienda['citta'] ?? 'N/A'); ?></p>
            <p><strong>Paese:</strong> <?php echo htmlspecialchars($azienda['paese'] ?? 'N/A'); ?></p>
            <p><strong>Numero di Telefono:</strong> <?php echo htmlspecialchars($azienda['numero_telefono'] ?? 'N/A'); ?></p>
            <p><strong>Email:</strong> 
                <?php if (!empty($azienda['email'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars($azienda['email']); ?>"><?php echo htmlspecialchars($azienda['email']); ?></a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </p>
            <p><strong>Data Inizio Attività:</strong> <?php echo htmlspecialchars($azienda['data_inizio_attivita'] ?? 'N/A'); ?></p>
            <p><strong>Descrizione:</strong> <?php echo nl2br(htmlspecialchars($azienda['descrizione'] ?? 'N/A')); ?></p>
            <p><strong>Stato:</strong> <?php echo htmlspecialchars($azienda['stato_azienda'] ?? 'N/A'); ?></p>
        </div>
    </div>
    <div class="mt-4">
        <a href="/aziende_operatrici.php" class="btn btn-secondary">Torna alla pagina Aziende Operatrici</a>
    </div>
</div>

<?php include 'footer.php'; // Include il footer ?>
