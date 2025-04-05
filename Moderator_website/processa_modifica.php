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

// Iniziamo la transazione per assicurare che tutte le modifiche vengano applicate atomicamente
$pdo->beginTransaction();

try {
    // Switch per la gestione della modifica in base alla tabella destinazione
    switch ($tabella_destinazione) {
        case 'nazioni':
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

    // Elimina tutte le modifiche con lo stesso id_gruppo_modifica
    $delete_query = "DELETE FROM modifiche_in_sospeso WHERE id_gruppo_modifica = :id_gruppo_modifica";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica, PDO::PARAM_INT);
    $delete_stmt->execute();

    echo "Modifiche applicate correttamente e tutte le modifiche in sospeso sono state rimosse!";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Errore nell'applicare le modifiche: " . $e->getMessage();
}

?>
