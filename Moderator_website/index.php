<?php
// Abilita la visualizzazione degli errori durante lo sviluppo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Collegamento al database
require_once 'Utilities/dbconnect.php';

try {
    // Recupera il primo id_gruppo_modifica disponibile
    $query = "SELECT id_gruppo_modifica, tabella_destinazione FROM modifiche_in_sospeso WHERE stato = 'In attesa' ORDER BY data_richiesta ASC LIMIT 1";
    $stmt = $pdo->query($query);
    $first_group = $stmt->fetch(PDO::FETCH_ASSOC);

    $modifiche = [];
    $tabella_destinazione = "N/D";

    if ($first_group) {
        $id_gruppo_modifica = $first_group['id_gruppo_modifica'];
        $tabella_destinazione = $first_group['tabella_destinazione'];

        // Recupera tutte le modifiche associate a quell'id_gruppo_modifica
        $query = "
            SELECT campo_modificato, valore_nuovo, valore_vecchio, stato, autore
            FROM modifiche_in_sospeso
            WHERE id_gruppo_modifica = :id_gruppo_modifica
            ORDER BY campo_modificato ASC
        ";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica, PDO::PARAM_INT);
        $stmt->execute();
        $modifiche = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Errore database: " . $e->getMessage());
}

// Funzione per gestire immagini e video con il percorso corretto
function formatMedia($value) {
    if (!$value) {
        return "<i>Nessun valore</i>";
    }

    // Se il valore è un link assoluto (http/https), lo lasciamo invariato
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        $file_path = $value;
    } else {
        // Se è un percorso relativo, aggiungiamo '../Website/' davanti
        $file_path = "../Website/" . ltrim($value, '/');
    }

    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

    // Se è un'immagine
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        return "<img src='$file_path' alt='Immagine' style='max-width: 200px; max-height: 200px;'>";
    }

    // Se è un video
    if (in_array($ext, ['mp4', 'webm', 'ogg'])) {
        return "<video controls style='max-width: 300px; max-height: 200px;'><source src='$file_path' type='video/$ext'>Il tuo browser non supporta il video.</video>";
    }

    // Se non è né immagine né video, mostriamo un link
    return "<a href='$file_path' target='_blank'>" . htmlspecialchars($file_path) . "</a>";
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
        <h2>Tabella: <?= htmlspecialchars($tabella_destinazione) ?> | ID Gruppo: <?= htmlspecialchars($id_gruppo_modifica) ?></h2>
        <table border="1">
            <tr>
                <th>Campo Modificato</th>
                <th>Nuovo Valore</th>
                <th>Vecchio Valore</th>
                <th>Autore</th>
            </tr>
            <?php foreach ($modifiche as $modifica): ?>
                <tr>
                    <td><?= htmlspecialchars($modifica['campo_modificato']) ?></td>
                    <td><?= formatMedia($modifica['valore_nuovo']) ?></td>
                    <td><?= $modifica['valore_vecchio'] !== null ? formatMedia($modifica['valore_vecchio']) : "<i>Nessun valore precedente</i>" ?></td>
                    <td><?= htmlspecialchars($modifica['autore']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
