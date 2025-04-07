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

<!-- Importa la libreria Font Awesome per le icone -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<div class="container">
    <?php if (!empty($azienda)): ?>
        <?php if (!empty($azienda['logo'])): ?>
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="<?php echo htmlspecialchars($azienda['logo']); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars($azienda['logo']); ?>" alt="Logo di <?php echo htmlspecialchars($azienda['nome']); ?>" style="max-width: 200px; height: auto;" />
                </a>
            </div>
        <?php endif; ?>
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <h1><?php echo htmlspecialchars($azienda['nome']); ?></h1>
            <a href="/modifiche/modifica_azienda_costruttrice.php?id=<?php echo urlencode($azienda['id_azienda']); ?>" 
               class="btn btn-warning btn-sm d-flex align-items-center justify-content-center" 
               style="width: 40px; height: 40px; font-size: 16px;">
                <i class="fas fa-pencil-alt"></i> <!-- Icona matita -->
            </a>
        </div>
        <p><strong>Descrizione breve:</strong> <?php echo htmlspecialchars($azienda['short_desc'] ?? 'N/A'); ?></p>
        <p><strong>Descrizione lunga:</strong> <?php echo nl2br(htmlspecialchars($azienda['long_desc'] ?? 'N/A')); ?></p>
        <p><strong>Fondazione:</strong> <?php echo htmlspecialchars($azienda['fondazione'] ?? 'N/A'); ?></p>
        <p><strong>Chiusura:</strong> <?php echo htmlspecialchars($azienda['chiusura'] ?? 'N/A'); ?></p>
        <p><strong>Sede:</strong> <?php echo htmlspecialchars($azienda['sede'] ?? 'N/A'); ?></p>
        <p><strong>Nazione:</strong> <?php echo htmlspecialchars($azienda['nazione'] ?? 'N/A'); ?></p>
        <p><strong>Sito Web:</strong> 
            <?php if (!empty($azienda['sito_web'])): ?>
                <a href="<?php echo htmlspecialchars($azienda['sito_web']); ?>" target="_blank"><?php echo htmlspecialchars($azienda['sito_web']); ?></a>
            <?php else: ?>
                N/A
            <?php endif; ?>
        </p>
        <p><strong>Stato:</strong> <?php echo htmlspecialchars($azienda['stato'] ?? 'N/A'); ?></p>
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
    <div class="mt-4">
        <a href="/aziende_costruttrici.php" class="btn btn-secondary">Torna alla pagina Aziende Costruttrici</a>
    </div>
</div>

<?php include 'footer.php'; // Include il footer ?>
