<?php
// Abilita la visualizzazione degli errori durante lo sviluppo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Collegamento al database
require_once 'Utilities/dbconnect.php';

try {
    // Recupera il primo id_gruppo_modifica disponibile
    $query = "SELECT id_gruppo_modifica FROM modifiche_in_sospeso WHERE stato = 'In attesa' ORDER BY data_richiesta ASC LIMIT 1";
    $stmt = $pdo->query($query);
    $first_group = $stmt->fetch(PDO::FETCH_ASSOC);

    $modifiche = [];
    if ($first_group) {
        $id_gruppo_modifica = $first_group['id_gruppo_modifica'];

        // Recupera tutte le modifiche associate a quell'id_gruppo_modifica
        $query = "
            SELECT tabella_destinazione, campo_modificato, valore_nuovo, valore_vecchio, stato, autore, data_richiesta
            FROM modifiche_in_sospeso
            WHERE id_gruppo_modifica = :id_gruppo_modifica
            ORDER BY data_richiesta ASC
        ";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica, PDO::PARAM_INT);
        $stmt->execute();
        $modifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Errore database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifiche in sospeso</title>
</head>
<body>
    <h1>Modifiche in Sospeso</h1>

    <?php if (empty($modifiche)): ?>
        <p>Nessuna modifica in attesa.</p>
    <?php else: ?>
        <h2>Modifiche per il gruppo ID: <?= htmlspecialchars($id_gruppo_modifica) ?></h2>
        <table border="1">
            <tr>
                <th>Tabella</th>
                <th>Campo Modificato</th>
                <th>Nuovo Valore</th>
                <th>Vecchio Valore</th>
                <th>Autore</th>
                <th>Data Richiesta</th>
            </tr>
            <?php foreach ($modifiche as $modifica): ?>
                <tr>
                    <td><?= htmlspecialchars($modifica['tabella_destinazione']) ?></td>
                    <td><?= htmlspecialchars($modifica['campo_modificato']) ?></td>
                    <td><?= htmlspecialchars($modifica['valore_nuovo']) ?></td>
                    <td><?= isset($modifica['valore_vecchio']) ? htmlspecialchars($modifica['valore_vecchio']) : "<i>Nessun valore precedente</i>" ?></td>
                    <td><?= htmlspecialchars($modifica['autore']) ?></td>
                    <td><?= htmlspecialchars($modifica['data_richiesta']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
