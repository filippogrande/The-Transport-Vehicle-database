<?php
// Abilita la visualizzazione degli errori durante lo sviluppo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Collegamento al database
require_once '../Utilities/dbconnect.php';

// Query per ottenere tutte le modifiche in sospeso raggruppate per id_gruppo_modifica
$query = "
    SELECT id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, valore_vecchio, stato, autore, data_richiesta
    FROM modifiche_in_sospeso
    WHERE stato = 'In attesa'
    ORDER BY id_gruppo_modifica
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$modifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Raggruppamento delle modifiche per id_gruppo_modifica
$modifiche_raggruppate = [];
foreach ($modifiche as $modifica) {
    $modifiche_raggruppate[$modifica['id_gruppo_modifica']][] = $modifica;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Modifiche - Moderatore</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Modifiche in Sospeso</h1>

    <?php foreach ($modifiche_raggruppate as $id_gruppo => $modifiche): ?>
        <div class="card mb-3">
            <div class="card-header">
                <strong>Modifiche Gruppo ID: <?= $id_gruppo ?></strong>
            </div>
            <div class="card-body">
                <?php foreach ($modifiche as $modifica): ?>
                    <div class="mb-3">
                        <h5>Tabella: <?= htmlspecialchars($modifica['tabella_destinazione']) ?></h5>
                        <p><strong>Campo Modificato:</strong> <?= htmlspecialchars($modifica['campo_modificato']) ?></p>
                        <p><strong>Nuovo Valore:</strong> <?= htmlspecialchars($modifica['valore_nuovo']) ?></p>
                        <p><strong>Vecchio Valore:</strong> <?= htmlspecialchars($modifica['valore_vecchio']) ?: 'Nessun valore precedente' ?></p>
                        <p><strong>Autore:</strong> <?= htmlspecialchars($modifica['autore']) ?></p>
                        <p><strong>Data Richiesta:</strong> <?= htmlspecialchars($modifica['data_richiesta']) ?></p>
                        <div class="btn-group" role="group" aria-label="Modifiche">
                            <form action="approva_negazione.php" method="POST">
                                <input type="hidden" name="id_modifica" value="<?= $modifica['id_modifica'] ?>">
                                <button type="submit" name="azione" value="approva" class="btn btn-success">Approva</button>
                                <button type="submit" name="azione" value="nega" class="btn btn-danger">Nega</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>
