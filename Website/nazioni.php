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
    // Recupero delle nazioni dal database
    $query = "SELECT * FROM nazione ORDER BY nome";
    $stmt = $pdo->query($query);

    // Controlla se la query è stata eseguita correttamente
    if ($stmt === false) {
        throw new Exception("Errore nella query: " . implode(", ", $pdo->errorInfo()));
    }

    // Recupero dei dati
    $nazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Controllo se ci sono dati
    if (empty($nazioni)) {
        $nazioni_messaggio = "Nessuna nazione trovata nel database.";
    } else {
        $nazioni_messaggio = "";
    }
} catch (PDOException $e) {
    // Gestione degli errori di query
    $nazioni_messaggio = "Errore nella query: " . $e->getMessage();
} catch (Exception $e) {
    // Gestione di altri errori
    $nazioni_messaggio = "Errore generico: " . $e->getMessage();
}

include 'header.html'; // Includi il file header.php

<!-- Aggiungi il collegamento a Font Awesome -->
 <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
?>

<!-- Visualizzazione del messaggio (se presente) e della tabella -->
<div class="container mt-4">
    <h1 class="mb-3">Nazioni</h1>
    <a href="Aggiunte/crea_nazione.php" class="btn btn-primary mb-3 ms-auto d-block" style="width: auto;">Crea Nuova Nazione</a>


    <?php if ($nazioni_messaggio): ?>
        <div class="alert alert-warning"><?php echo $nazioni_messaggio; ?></div>
    <?php else: ?>
        <h2 class="mb-3">Elenco delle Nazioni</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>ISO 3</th>
                    <th>ISO 2</th>
                    <th>Continente</th>
                    <th>Capitale</th>
                    <th>Bandiera</th> <!-- Aggiunta colonna Bandiera -->
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nazioni as $nazione): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nazione['nome'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($nazione['codice_iso'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($nazione['codice_iso2'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($nazione['continente'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($nazione['capitale'] ?? ''); ?></td>
                        <td>
                            <?php if (!empty($nazione['bandiera'])): ?>
                                <img src="<?php echo htmlspecialchars($nazione['bandiera']); ?>" alt="Bandiera di <?php echo htmlspecialchars($nazione['nome'] ?? ''); ?>" style="max-width: 100px; max-height: 50px;">
                            <?php else: ?>
                                <span>Nessuna bandiera</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/modifiche/modifica_nazione.php?nome=<?php echo urlencode($nazione['nome'] ?? ''); ?>" class="btn btn-warning btn-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 16px;">
                                <i class="fas fa-pencil-alt"></i> <!-- Solo icona matita -->
                            </a>
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
