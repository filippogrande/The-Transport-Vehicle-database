<?php
require_once 'db_connection.php'; // Connessione al database
include 'header.html'; // Include l'header

$id_azienda = $_GET['id'] ?? null;

if ($id_azienda) {
    $query = "SELECT * FROM azienda_costruttrice WHERE id_azienda = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_azienda);
    $stmt->execute();
    $result = $stmt->get_result();
    $azienda = $result->fetch_assoc();
    $stmt->close();
}
?>

<div class="container">
    <?php if (!empty($azienda)): ?>
        <h1><?php echo htmlspecialchars($azienda['nome']); ?></h1>
        <p><strong>Descrizione breve:</strong> <?php echo htmlspecialchars($azienda['short_desc']); ?></p>
        <p><strong>Descrizione lunga:</strong> <?php echo nl2br(htmlspecialchars($azienda['long_desc'])); ?></p>
        <p><strong>Fondazione:</strong> <?php echo htmlspecialchars($azienda['fondazione']); ?></p>
        <p><strong>Chiusura:</strong> <?php echo htmlspecialchars($azienda['chiusura'] ?? 'N/A'); ?></p>
        <p><strong>Sede:</strong> <?php echo htmlspecialchars($azienda['sede']); ?></p>
        <p><strong>Nazione:</strong> <?php echo htmlspecialchars($azienda['nazione']); ?></p>
        <p><strong>Sito Web:</strong> <a href="<?php echo htmlspecialchars($azienda['sito_web']); ?>" target="_blank"><?php echo htmlspecialchars($azienda['sito_web']); ?></a></p>
        <p><strong>Stato:</strong> <?php echo htmlspecialchars($azienda['stato']); ?></p>
        <?php if (!empty($azienda['logo'])): ?>
            <p><strong>Logo:</strong></p>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($azienda['logo']); ?>" alt="Logo di <?php echo htmlspecialchars($azienda['nome']); ?>" />
        <?php endif; ?>
        <?php if (!empty($azienda['id_successore'])): ?>
            <p><strong>Successore:</strong> 
                <a href="azienda_costruttrice.php?id=<?php echo urlencode($azienda['id_successore']); ?>">
                    <?php
                    $query_successore = "SELECT nome FROM azienda_costruttrice WHERE id_azienda = ?";
                    $stmt_successore = $conn->prepare($query_successore);
                    $stmt_successore->bind_param("i", $azienda['id_successore']);
                    $stmt_successore->execute();
                    $result_successore = $stmt_successore->get_result();
                    $successore = $result_successore->fetch_assoc();
                    echo htmlspecialchars($successore['nome'] ?? 'N/A');
                    $stmt_successore->close();
                    ?>
                </a>
            </p>
        <?php endif; ?>
    <?php else: ?>
        <p>Azienda non trovata.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; // Include il footer ?>
