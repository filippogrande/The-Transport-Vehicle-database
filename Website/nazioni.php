<?php
// Abilita la visualizzazione degli errori durante lo sviluppo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Collegamento al database
try {
    require_once 'Utilities/dbconnect.php'; // Verifica la connessione al database
    if (!$pdo) {
        throw new Exception("Errore nella connessione al database.");
    } else {
        // Connessione riuscita
        echo "Connessione al database riuscita!<br>"; // Puoi rimuovere questa linea in produzione
    }
} catch (Exception $e) {
    die("Errore di connessione: " . $e->getMessage());
}

try {
    // Recupero delle nazioni dal database
    $query = "SELECT * FROM nazione ORDER BY nome";
    $stmt = $pdo->query($query);

    // Controlla se la query è stata eseguita correttamente
    if ($stmt === false) {
        throw new Exception("Errore nella query: " . implode(", ", $pdo->errorInfo()));
    }

    // Recupero dei dati
    $nazioni = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Controllo se ci sono dati
    if (empty($nazioni)) {
        echo "Nessuna nazione trovata nel database.<br>"; // Messaggio se non ci sono dati
    } else {
        // Includi la navbar
        include 'navbar.html'; // Aggiungi il percorso corretto se necessario

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
            <h1 class='mb-3'>Nazioni</h1>
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

        // Ciclo attraverso i dati e li stampo nella tabella
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
    }

} catch (PDOException $e) {
    // Gestione degli errori di query
    echo "Errore nella query: " . $e->getMessage();
} catch (Exception $e) {
    // Gestione di altri errori
    echo "Errore generico: " . $e->getMessage();
}
?>
