<?php
require_once 'utilities/dbconnect.php'; // Collegamento al database

try {
    $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
    $result = $pdo->query($query);
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);

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

    foreach ($tables as $table) {
        $countQuery = "SELECT COUNT(*) FROM \"$table\"";
        $countResult = $pdo->query($countQuery);
        $rowCount = $countResult->fetchColumn();

        echo "<tr>
                <td>$table</td>
                <td>$rowCount</td>
              </tr>";
    }

    echo "  </tbody>
        </table>
    </body>
    </html>";

} catch (PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?>
