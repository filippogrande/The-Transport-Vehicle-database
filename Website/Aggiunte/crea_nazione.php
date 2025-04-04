<?php
// Abilita la visualizzazione degli errori durante lo sviluppo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Collegamento al database
require_once '../Utilities/dbconnect.php';  // Corretto il percorso

// Verifica la connessione al database
if (!$pdo) {
    die("Errore nella connessione al database.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ottieni i dati inviati dal form
    $nome = $_POST['nome'];
    $codice_iso = $_POST['codice_iso'];
    $codice_iso2 = $_POST['codice_iso2'];
    $continente = $_POST['continente'];
    $capitale = $_POST['capitale'];
    $bandiera = $_POST['bandiera']; // Assumiamo che la bandiera sia una URL o un dato BLOB (puoi modificarlo in base al tipo)

    // Raggruppiamo l'operazione in un gruppo di modifiche
    $id_gruppo_modifica = rand(1000, 9999); // Puoi utilizzare un ID unico o un altro metodo

    // Inseriamo i dati nella tabella `modifiche_in_sospeso`
    try {
        $query = "INSERT INTO modifiche_in_sospeso (id_gruppo_modifica, tabella_destinazione, campo_modificato, valore_nuovo, stato, autore) 
                  VALUES (:id_gruppo_modifica, 'nazione', 'nome', :nome, 'In attesa', 'admin'),
                         (:id_gruppo_modifica, 'nazione', 'codice_iso', :codice_iso, 'In attesa', 'admin'),
                         (:id_gruppo_modifica, 'nazione', 'codice_iso2', :codice_iso2, 'In attesa', 'admin'),
                         (:id_gruppo_modifica, 'nazione', 'continente', :continente, 'In attesa', 'admin'),
                         (:id_gruppo_modifica, 'nazione', 'capitale', :capitale, 'In attesa', 'admin'),
                         (:id_gruppo_modifica, 'nazione', 'bandiera', :bandiera, 'In attesa', 'admin')";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':codice_iso', $codice_iso);
        $stmt->bindParam(':codice_iso2', $codice_iso2);
        $stmt->bindParam(':continente', $continente);
        $stmt->bindParam(':capitale', $capitale);
        $stmt->bindParam(':bandiera', $bandiera);

        $stmt->execute();

        echo "La nazione è stata proposta con successo. In attesa di approvazione.";
    } catch (PDOException $e) {
        echo "Errore nell'inserimento della modifica: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Nazione</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Crea Nuova Nazione</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome della Nazione</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="mb-3">
            <label for="codice_iso" class="form-label">Codice ISO (Alpha-3)</label>
            <input type="text" class="form-control" id="codice_iso" name="codice_iso" maxlength="3" required>
        </div>
        <div class="mb-3">
            <label for="codice_iso2" class="form-label">Codice ISO (Alpha-2)</label>
            <input type="text" class="form-control" id="codice_iso2" name="codice_iso2" maxlength="2" required>
        </div>
        <div class="mb-3">
            <label for="continente" class="form-label">Continente</label>
            <input type="text" class="form-control" id="continente" name="continente" required>
        </div>
        <div class="mb-3">
            <label for="capitale" class="form-label">Capitale</label>
            <input type="text" class="form-control" id="capitale" name="capitale" required>
        </div>
        <div class="mb-3">
            <label for="bandiera" class="form-label">URL Bandiera</label>
            <input type="text" class="form-control" id="bandiera" name="bandiera">
        </div>
        <button type="submit" class="btn btn-primary">Proponi Nazione</button>
    </form>
</body>
</html>

