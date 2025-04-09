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

    // Verifica che il nome della variante sia stato fornito
    if (empty($nome)) {
        die("Errore: Il nome della variante è obbligatorio.");
    }

    // ID gruppo modifica per raggruppare le modifiche
    $id_gruppo_modifica = rand(1000, 9999);

    // Inserisci i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'modello', :campo_modificato, :valore_nuovo, 'In attesa', 'admin')";
        $stmt = $pdo->prepare($query);

        $campi = [
            'nome' => $nome,
            'tipo' => $tipo,
            'anno_inizio_produzione' => $anno_inizio_produzione,
            'anno_fine_produzione' => $anno_fine_produzione,
            'lunghezza' => $lunghezza,
            'larghezza' => $larghezza,
            'altezza' => $altezza,
            'peso' => $peso,
            'motorizzazione' => $motorizzazione,
            'velocita_massima' => $velocita_massima,
            'descrizione' => $descrizione,
            'totale_veicoli' => $totale_veicoli,
        ];

        foreach ($campi as $campo => $valore_nuovo) {
            if ($valore_nuovo !== null) {
                $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
                $stmt->bindParam(':campo_modificato', $campo);
                $stmt->bindParam(':valore_nuovo', $valore_nuovo);
                $stmt->execute();
            }
        }

        // Inserisci la relazione nella tabella `modifiche_in_sospeso` per `variante_modello`
        $query_variante = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, id_entita, campo_modificato, valore_nuovo, stato, autore) 
                           VALUES (:id_gruppo_modifica, 'variante_modello', :id_entita, 'id_modello_variante', :id_modello_variante, 'In attesa', 'admin')";
        $stmt_variante = $pdo->prepare($query_variante);
        $stmt_variante->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
        $stmt_variante->bindParam(':id_entita', $id_modello_base, PDO::PARAM_INT);
        $stmt_variante->bindParam(':id_modello_variante', $nome); // Nome come identificativo temporaneo
        $stmt_variante->execute();

        echo "La variante del modello è stata proposta con successo. In attesa di approvazione.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento della proposta: " . $e->getMessage();
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
        <button type="submit" class="btn btn-primary">Proponi Variante</button>
        <a href="../modello.php?id=<?php echo urlencode($id_modello_base); ?>" class="btn btn-secondary">Annulla</a>
    </form>
</body>
</html>
