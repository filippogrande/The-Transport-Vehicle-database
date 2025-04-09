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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $nome = trim($_POST['nome']);
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : $modello_base['tipo'];
    $anno_inizio_produzione = isset($_POST['anno_inizio_produzione']) ? trim($_POST['anno_inizio_produzione']) : null;
    $anno_fine_produzione = isset($_POST['anno_fine_produzione']) ? trim($_POST['anno_fine_produzione']) : null;
    $lunghezza = isset($_POST['lunghezza']) ? trim($_POST['lunghezza']) : $modello_base['lunghezza'];
    $larghezza = isset($_POST['larghezza']) ? trim($_POST['larghezza']) : $modello_base['larghezza'];
    $altezza = isset($_POST['altezza']) ? trim($_POST['altezza']) : $modello_base['altezza'];
    $peso = isset($_POST['peso']) ? trim($_POST['peso']) : $modello_base['peso'];
    $motorizzazione = isset($_POST['motorizzazione']) ? trim($_POST['motorizzazione']) : $modello_base['motorizzazione'];
    $velocita_massima = isset($_POST['velocita_massima']) ? trim($_POST['velocita_massima']) : $modello_base['velocita_massima'];
    $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : null;
    $totale_veicoli = isset($_POST['totale_veicoli']) ? trim($_POST['totale_veicoli']) : 0;

    // Inserisci il nuovo modello
    try {
        $query = "INSERT INTO modello (nome, tipo, anno_inizio_produzione, anno_fine_produzione, lunghezza, larghezza, altezza, peso, motorizzazione, velocita_massima, descrizione, totale_veicoli) 
                  VALUES (:nome, :tipo, :anno_inizio_produzione, :anno_fine_produzione, :lunghezza, :larghezza, :altezza, :peso, :motorizzazione, :velocita_massima, :descrizione, :totale_veicoli)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':anno_inizio_produzione', $anno_inizio_produzione);
        $stmt->bindParam(':anno_fine_produzione', $anno_fine_produzione);
        $stmt->bindParam(':lunghezza', $lunghezza);
        $stmt->bindParam(':larghezza', $larghezza);
        $stmt->bindParam(':altezza', $altezza);
        $stmt->bindParam(':peso', $peso);
        $stmt->bindParam(':motorizzazione', $motorizzazione);
        $stmt->bindParam(':velocita_massima', $velocita_massima);
        $stmt->bindParam(':descrizione', $descrizione);
        $stmt->bindParam(':totale_veicoli', $totale_veicoli);
        $stmt->execute();

        $id_modello_variante = $pdo->lastInsertId();

        // Inserisci la relazione nella tabella `variante_modello`
        $query_variante = "INSERT INTO variante_modello (id_modello_base, id_modello_variante) VALUES (:id_modello_base, :id_modello_variante)";
        $stmt_variante = $pdo->prepare($query_variante);
        $stmt_variante->bindParam(':id_modello_base', $id_modello_base, PDO::PARAM_INT);
        $stmt_variante->bindParam(':id_modello_variante', $id_modello_variante, PDO::PARAM_INT);
        $stmt_variante->execute();

        echo "La variante del modello è stata creata con successo.";
    } catch (PDOException $e) {
        echo "Errore nella creazione della variante: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Variante Modello</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Variante per "<?php echo htmlspecialchars($modello_base['nome']); ?>"</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome della Variante</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <!-- Campi opzionali per personalizzare la variante -->
        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" id="descrizione" name="descrizione" rows="5"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Crea Variante</button>
        <a href="../modello.php?id=<?php echo urlencode($id_modello_base); ?>" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
