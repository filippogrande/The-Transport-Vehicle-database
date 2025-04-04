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

include 'navbar.html';

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

include 'header.php'; // Include l'HTML separato

?>

<!-- Visualizzazione del messaggio (se presente) e della tabella -->
<div class="container mt-4">
    <h1 class="mb-3">Nazioni</h1>
    <a href="Aggiunte/crea_nazione.php" class="btn btn-primary mb-3">Crea Nuova Nazione</a>

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
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nazioni as $nazione): ?>
                    <tr>
                        <td><?php echo $nazione['nome']; ?></td>
                        <td><?php echo $nazione['codice_iso']; ?></td>
                        <td><?php echo $nazione['codice_iso2']; ?></td>
                        <td><?php echo $nazione['continente']; ?></td>
                        <td><?php echo $nazione['capitale']; ?></td>
                        <td>
                            <a href="modifica_nazione.php?nome=<?php echo $nazione['nome']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
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
