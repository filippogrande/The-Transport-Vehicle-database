<?php
require_once 'utilities/dbconnect.php'; // Collegamento al database

try {
    // Recupero delle nazioni dal database
    $query = "SELECT nome, codice_iso, codice_iso2, continente, capitale FROM nazione ORDER BY nome";
    $stmt = $pdo->query($query);
    $nazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<!DOCTYPE html>
    <html lang='it'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Elenco Nazioni</title>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        <script src='https://kit.fontawesome.com/your-fontawesome-kit.js' crossorigin='anonymous'></script>
    </head>
    <body class='container mt-4'>
        <h2 class='mb-3'>Elenco delle Nazioni</h2>
        <table class='table table-bordered table-striped'>
            <thead class='table-dark'>
                <tr>
                    <th>Nome</th>
                    <th>ISO 3</th>
                    <th>ISO 2</th>
                    <th>Continente</th>
                    <th>Capitale</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>";

    foreach ($nazioni as $nazione) {
        echo "<tr>
                <td>{$nazione['nome']}</td>
                <td>{$nazione['codice_iso']}</td>
                <td>{$nazione['codice_iso2']}</td>
                <td>{$nazione['continente']}</td>
                <td>{$nazione['capitale']}</td>
                <td>
                    <a href='modifica_nazione.php?nome={$nazione['nome']}' class='btn btn-warning btn-sm'>
                        <i class='fas fa-edit'></i>
                    </a>
                </td>
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
