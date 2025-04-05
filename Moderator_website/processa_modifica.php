<?php

require_once 'Utilities/dbconnect.php';

// Ricevi i dati dal form tramite GET
$id_gruppo_modifica = $_GET['id_gruppo_modifica'] ?? null;
$tabella_destinazione = $_GET['tabella_destinazione'] ?? null;
$modifiche_selezionate = $_GET['modifica_selezionata'] ?? [];

// Verifica che siano stati inviati i dati
if (empty($modifiche_selezionate)) {
    $delete_query = "DELETE FROM modifiche_in_sospeso WHERE id_gruppo_modifica = :id_gruppo_modifica";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica, PDO::PARAM_INT);
    $delete_stmt->execute();
    echo "Nessuna modifica selezionata. Modifiche eliminate.";
    exit;
}

echo "<h2>Modifiche selezionate da approvare:</h2>";

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<thead><tr>
        <th>ID Modifica</th>
        <th>Tabella Destinazione</th>
        <th>Campo Modificato</th>
        <th>ID Entità</th>
        <th>Valore Nuovo</th>
      </tr></thead>";
echo "<tbody>";

foreach ($modifiche_selezionate as $id_modifica) {
    $query = "SELECT * FROM modifiche_in_sospeso WHERE id_modifica = :id_modifica";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_modifica', $id_modifica, PDO::PARAM_INT);
    $stmt->execute();
    $modifica = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($modifica) {
        echo "<tr>
                <td>{$modifica['id_modifica']}</td>
                <td>{$modifica['tabella_destinazione']}</td>
                <td>{$modifica['campo_modificato']}</td>
                <td>{$modifica['id_entita']}</td>
                <td>{$modifica['valore_nuovo']}</td>
              </tr>";
    }
}

echo "</tbody></table><br><hr>";

// Iniziamo la transazione per assicurare che tutte le modifiche vengano applicate atomicamente
$pdo->beginTransaction();

try {
    // Switch per la gestione della modifica in base alla tabella destinazione
    switch ($tabella_destinazione) {
        case 'nazione':
            include 'gestisci_nazione.php';
            break;

        case 'tabella_2':
            include 'gestisci_tabella_2.php';
            break;

        // Aggiungi altri casi per le altre tabelle
        default:
            echo "Tabella non supportata. tabella_destinazione: $tabella_destinazione";
            exit;
    }

    // Se tutte le modifiche sono state applicate senza errori, confermiamo la transazione
    $pdo->commit();

    echo "Modifiche applicate correttamente e tutte le modifiche in sospeso sono state rimosse!";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Errore nell'applicare le modifiche: " . $e->getMessage();
}

?>
