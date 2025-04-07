<?php
// Abilita la visualizzazione degli errori durante lo sviluppo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Collegamento al database
try {
    require_once 'Utilities/dbconnect.php'; // Verifica la connessione al database
    if (!$pdo) {
        throw new Exception("Errore nella connessione al database.");
    }
} catch (Exception $e) {
    die("Errore di connessione: " . $e->getMessage());
}

try {
    // Recupero delle aziende dal database
    $query = "SELECT * FROM azienda_costruttrice ORDER BY nome";
    $stmt = $pdo->query($query);

    // Controlla se la query è stata eseguita correttamente
    if ($stmt === false) {
        throw new Exception("Errore nella query: " . implode(", ", $pdo->errorInfo()));
    }

    // Recupero dei dati
    $aziende = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Controllo se ci sono dati
    if (empty($aziende)) {
        $aziende_messaggio = "Nessuna azienda trovata nel database.";
    } else {
        $aziende_messaggio = "";
    }
} catch (PDOException $e) {
    // Gestione degli errori di query
    $aziende_messaggio = "Errore nella query: " . $e->getMessage();
} catch (Exception $e) {
    // Gestione di altri errori
    $aziende_messaggio = "Errore generico: " . $e->getMessage();
}

include 'header.html'; // Includi il file header.php
?>

<!-- Visualizzazione del messaggio (se presente) e della tabella -->
<div class="container mt-4">
    <h1 class="mb-3">Aziende Costruttrici</h1>
    <a href="Aggiunte/crea_azienda_costuttrice.php" class="btn btn-primary mb-3 ms-auto d-block" style="width: auto;">Crea Nuova Azienda</a>

    <?php if ($aziende_messaggio): ?>
        <div class="alert alert-warning"><?php echo $aziende_messaggio; ?></div>
    <?php else: ?>
        <h2 class="mb-3">Elenco delle Aziende</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Descrizione Breve</th>
                    <th>Nazione</th>
                    <th>Sito Web</th>
                    <th>Stato</th>
                    <th>Logo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aziende as $azienda): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($azienda['nome'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($azienda['short_desc'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($azienda['nazione'] ?? ''); ?></td>
                        <td>
                            <?php if (!empty($azienda['sito_web'])): ?>
                                <a href="<?php echo htmlspecialchars($azienda['sito_web']); ?>" target="_blank">Visita</a>
                            <?php else: ?>
                                <span>Nessun sito</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($azienda['stato'] ?? ''); ?></td>
                        <td>
                            <?php if (!empty($azienda['logo'])): ?>
                                <img src="<?php echo htmlspecialchars($azienda['logo']); ?>" alt="Logo di <?php echo htmlspecialchars($azienda['nome'] ?? ''); ?>" style="max-width: 100px; max-height: 50px;">
                            <?php else: ?>
                                <span>Nessun logo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
include 'footer.php'; // Includi il footer separato
?>
