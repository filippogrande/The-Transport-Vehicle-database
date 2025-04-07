<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

include 'header.html'; // Include l'header

$id_azienda = $_GET['id'] ?? null;

if ($id_azienda) {
    $query = "SELECT * FROM azienda_costruttrice WHERE id_azienda = :id_azienda";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_azienda', $id_azienda, PDO::PARAM_INT);
    $stmt->execute();
    $azienda = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container">
    <?php if (!empty($azienda)): ?>
        <?php if (!empty($azienda['logo'])): ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="<?php echo htmlspecialchars($azienda['logo']); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars($azienda['logo']); ?>" alt="Logo di <?php echo htmlspecialchars($azienda['nome']); ?>" style="max-width: 200px; height: auto;" />
                </a>
            </div>
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($azienda['nome']); ?></h1>
        <p><strong>Descrizione breve:</strong> <?php echo htmlspecialchars($azienda['short_desc']); ?></p>
        <p><strong>Descrizione lunga:</strong> <?php echo nl2br(htmlspecialchars($azienda['long_desc'])); ?></p>
        <p><strong>Fondazione:</strong> <?php echo htmlspecialchars($azienda['fondazione']); ?></p>
        <p><strong>Chiusura:</strong> <?php echo htmlspecialchars($azienda['chiusura'] ?? 'N/A'); ?></p>
        <p><strong>Sede:</strong> <?php echo htmlspecialchars($azienda['sede']); ?></p>
        <p><strong>Nazione:</strong> <?php echo htmlspecialchars($azienda['nazione']); ?></p>
        <p><strong>Sito Web:</strong> <a href="<?php echo htmlspecialchars($azienda['sito_web']); ?>" target="_blank"><?php echo htmlspecialchars($azienda['sito_web']); ?></a></p>
        <p><strong>Stato:</strong> <?php echo htmlspecialchars($azienda['stato']); ?></p>
        <?php if (!empty($azienda['id_successore'])): ?>
            <p><strong>Successore:</strong> 
                <a href="azienda_costruttrice.php?id=<?php echo urlencode($azienda['id_successore']); ?>">
                    <?php
                    $query_successore = "SELECT nome FROM azienda_costruttrice WHERE id_azienda = :id_successore";
                    $stmt_successore = $pdo->prepare($query_successore);
                    $stmt_successore->bindParam(':id_successore', $azienda['id_successore'], PDO::PARAM_INT);
                    $stmt_successore->execute();
                    $successore = $stmt_successore->fetch(PDO::FETCH_ASSOC);
                    echo htmlspecialchars($successore['nome'] ?? 'N/A');
                    ?>
                </a>
            </p>
        <?php endif; ?>
    <?php else: ?>
        <p>Azienda non trovata.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include il footer ?>
