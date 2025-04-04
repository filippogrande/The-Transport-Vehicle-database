<?php
// Mostra errori
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Stampa i dati inviati tramite POST per il debug
echo "<pre>";
var_dump($_POST);  // Stampa tutte le variabili inviate tramite POST
echo "</pre>";

// Prosegui con il resto del codice...
require_once 'Utilities/dbconnect.php';

// Ricevi i dati dal form
$id_gruppo_modifica = $_POST['id_gruppo_modifica'] ?? '';
$tabella_destinazione = $_POST['tabella_destinazione'] ?? '';
$modifiche_selezionate = $_POST['modifica_selezionata'] ?? [];

// Verifica che siano stati inviati i dati
if (empty($modifiche_selezionate)) {
    $delete_query = "DELETE FROM modifiche_in_sospeso WHERE id_gruppo_modifica = :id_gruppo_modifica";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica, PDO::PARAM_INT);
    $delete_stmt->execute();
    exit;
}

// DEFINIZIONE FUNZIONE formatMedia()
function formatMedia($value) {
    if (!$value) {
        return "<i>Nessun valore</i>";
    }

    $value = trim($value);
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $video_extensions = ['mp4', 'webm', 'ogg'];
    $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));

    // Costruiamo il path corretto, puntando all'alias configurato in nginx
    $file_path = ltrim($value, '/');

    // Se è immagine
    if (in_array($ext, $image_extensions)) {
        return "<img src='$file_path' alt='Immagine' style='max-width: 200px; max-height: 200px;'>";
    }

    // Se è video
    if (in_array($ext, $video_extensions)) {
        return "<video controls style='max-width: 300px; max-height: 200px;'>
                    <source src='$file_path' type='video/$ext'>Il tuo browser non supporta il video.
                </video>";
    }

    // Se non è né immagine né video, mostra un link cliccabile
    return htmlspecialchars($value);
}

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

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifiche in sospeso</title>
    <!-- Bootstrap CSS -->
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

            <form action="processa_modifica.php" method="POST">
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
    <!-- Imposta l'id_modifica come valore della checkbox e la checkbox come selezionata di default -->
    <input type="checkbox" name="modifica_selezionata[]" value="<?= htmlspecialchars($modifica['id_modifica'] ?? '') ?>" checked>
</td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bottone per inviare il form -->
    <button type="submit" class="btn btn-primary">Invia Modifiche</button>
</form>

        <?php endif; ?>
    </div>

    <!-- Bootstrap JS (opzionale, solo se serve per interattività) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
