<?php
// Mostra errori
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php';

function formatMedia($value) {
    if (!$value) {
        return "<i>Nessun valore</i>";
    }

    $value = trim($value);
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $video_extensions = ['mp4', 'webm', 'ogg'];
    $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));

    $file_path = ltrim($value, '/');

    if (in_array($ext, $image_extensions)) {
        return "<img src='$file_path' alt='Immagine' style='max-width: 200px; max-height: 200px;'>";
    }

    if (in_array($ext, $video_extensions)) {
        return "<video controls style='max-width: 300px; max-height: 200px;'>
                    <source src='$file_path' type='video/$ext'>Il tuo browser non supporta il video.
                </video>";
    }

    return htmlspecialchars($value);
}

try {
    $query = "SELECT id_gruppo_modifica, tabella_destinazione FROM modifiche_in_sospeso WHERE stato = 'In attesa' ORDER BY data_richiesta ASC LIMIT 1";
    $stmt = $pdo->query($query);
    $first_group = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($first_group) {
        $id_gruppo_modifica = $first_group['id_gruppo_modifica'];
        $tabella_destinazione = $first_group['tabella_destinazione'];

        $query = "
            SELECT id_modifica, campo_modificato, valore_nuovo, valore_vecchio, stato, autore
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
    echo "Errore nel database: " . $e->getMessage();
    die();
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifiche in sospeso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <h1 class="mb-4 text-primary">Modifiche in Sospeso</h1>

    <?php if (empty($modifiche)): ?>
        <div class="alert alert-info">Nessuna modifica in attesa.</div>
    <?php else: ?>
        <div class="mb-4">
            <h5>
                <span class="badge bg-secondary">Tabella: <?= htmlspecialchars($tabella_destinazione) ?></span>
                <span class="badge bg-info text-dark">ID Gruppo: <?= htmlspecialchars($id_gruppo_modifica) ?></span>
            </h5>
        </div>

        <form id="modificaForm" action="processa_modifica.php" method="GET">
    <input type="hidden" name="id_gruppo_modifica" value="<?= htmlspecialchars($id_gruppo_modifica) ?>">
    <input type="hidden" name="tabella_destinazione" value="<?= htmlspecialchars($tabella_destinazione) ?>">

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Campo Modificato</th>
                    <th>Nuovo Valore</th>
                    <th>Vecchio Valore</th>
                    <th>Autore</th>
                    <th>Seleziona</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modifiche as $modifica): ?>
                    <tr>
                        <td><?= htmlspecialchars($modifica['campo_modificato']) ?></td>
                        <td><?= formatMedia($modifica['valore_nuovo']) ?></td>
                        <td><?= $modifica['valore_vecchio'] !== null ? formatMedia($modifica['valore_vecchio']) : "<i>Nessun valore precedente</i>" ?></td>
                        <td><?= htmlspecialchars($modifica['autore']) ?></td>
                        <td>
                            <input type="checkbox" name="modifica_selezionata[]" value="<?= htmlspecialchars($modifica['id_modifica']) ?>" checked>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-start gap-3 mt-3">
        <button type="button" class="btn btn-primary" onclick="submitForm()">Invia Modifiche</button>
    </div>
</form>

<script>
function submitForm() {
    // Ottieni i dati del form
    const form = document.getElementById('modificaForm');
    const tabella = form.querySelector('input[name="tabella_destinazione"]').value;

    // Cambia l'azione del form in base alla tabella e invia il form
    switch (tabella) {
        case 'nazione':
            form.action = 'gestisci_nazione.php';
            form.method = 'POST';
            form.submit();
            break;
        case 'tabella_2':
            form.action = 'gestisci_tabella_2.php';
            form.method = 'POST';
            form.submit();
            break;
        // Aggiungi altri casi se necessario
        default:
            alert('Tabella non supportata');
            break;
    }
}
</script>

    <?php endif; ?>
</div> 
</body>
</html>
