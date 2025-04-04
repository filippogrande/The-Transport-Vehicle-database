<?php
require_once 'utilities/dbconnect.php'; // Collegamento al database

try {
    // Ottieni tutte le tabelle dallo schema 'public'
    $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
    $result = $pdo->query($query);
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);

    // Inizia la struttura HTML della pagina
    echo "<!DOCTYPE html>
    <html lang='it'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Tabelle del Database</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    </head>
    <body class='container mt-4'>
        <h2 class='mb-3'>Tabelle del Database</h2>
        <table class='table table-bordered table-striped'>
            <thead class='table-dark'>
                <tr>
                    <th>Nome Tabella</th>
                    <th>Numero di Righe</th>
                </tr>
            </thead>
            <tbody>";

    // Loop attraverso tutte le tabelle
    foreach ($tables as $table) {
        // Controlla quante righe ci sono nella tabella
        $countQuery = "SELECT COUNT(*) FROM \"$table\"";
        $countResult = $pdo->query($countQuery);
        $rowCount = $countResult->fetchColumn();

        // Se la tabella è vuota, mostra "Nessun dato"
        if ($rowCount == 0) {
            $rowCount = "Nessun dato";
        }

        // Stampa la tabella con il numero di righe
        echo "<tr>
                <td>$table</td>
                <td>$rowCount</td>
              </tr>";
    }

    // Chiudi la struttura HTML
    echo "  </tbody>
        </table>
    </body>
    </html>";

} catch (PDOException $e) {
    // Gestione degli errori
    echo "<!DOCTYPE html>
    <html lang='it'><head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Tabelle del Database</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    </head>
    <body class='container mt-4'>
        <h2 class='mb-3'>Errore</h2>
        <div class='alert alert-danger' role='alert'>
            Errore: " . $e->getMessage() . "
        </div>
        <tbody> " . $e->getMessage();

}
?>
