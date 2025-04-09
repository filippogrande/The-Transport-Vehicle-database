<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../Utilities/dbconnect.php'; // Connessione al database con PDO

include '../header.html'; // Include l'header

$id_modello_base = $_GET['id_base'] ?? null;

if (!$id_modello_base) {
    die("Errore: ID del modello base non fornito.");
}

// Recupera i dettagli del modello base
$query = "SELECT * FROM modello WHERE id_modello = :id_modello_base";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_modello_base', $id_modello_base, PDO::PARAM_INT);
$stmt->execute();
$modello_base = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$modello_base) {
    die("Errore: Modello base non trovato.");
}

// Recupera tutti i modelli che possono essere collegati come varianti
$query_varianti = "SELECT id_modello, nome FROM modello WHERE id_modello != :id_modello_base";
$stmt_varianti = $pdo->prepare($query_varianti);
$stmt_varianti->bindParam(':id_modello_base', $id_modello_base, PDO::PARAM_INT);
$stmt_varianti->execute();
$modelli_varianti = $stmt_varianti->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_modello_variante = $_POST['id_modello_variante'] ?? null;

    if (!$id_modello_variante) {
        die("Errore: Nessuna variante selezionata.");
    }

    // Inserisci la relazione nella tabella `variante_modello`
    try {
        $query = "INSERT INTO variante_modello (id_modello_base, id_modello_variante) 
                  VALUES (:id_modello_base, :id_modello_variante)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_modello_base', $id_modello_base, PDO::PARAM_INT);
        $stmt->bindParam(':id_modello_variante', $id_modello_variante, PDO::PARAM_INT);
        $stmt->execute();

        echo "La variante è stata collegata con successo.";
    } catch (PDOException $e) {
        echo "Errore nel collegamento della variante: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collega Variante Modello</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Collega Variante per "<?php echo htmlspecialchars($modello_base['nome']); ?>"</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="id_modello_variante" class="form-label">Seleziona Variante</label>
            <select class="form-control" id="id_modello_variante" name="id_modello_variante" required>
                <option value="">Seleziona un modello</option>
                <?php foreach ($modelli_varianti as $modello): ?>
                    <option value="<?php echo htmlspecialchars($modello['id_modello']); ?>">
                        <?php echo htmlspecialchars($modello['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Collega Variante</button>
        <a href="../modello.php?id=<?php echo urlencode($id_modello_base); ?>" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
