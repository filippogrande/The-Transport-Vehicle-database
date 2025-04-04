<?php

require_once 'Utilities/dbconnect.php';

// Ricevi i dati dal form
$id_gruppo_modifica = $_POST['id_gruppo_modifica'];
$tabella_destinazione = $_POST['tabella_destinazione'];
$modifiche_selezionate = $_POST['modifica_selezionata']; // Gli ID delle modifiche selezionate

// Verifica che siano stati inviati i dati
if (empty($modifiche_selezionate)) {
    $delete_query = "DELETE FROM modifiche_in_sospeso WHERE id_gruppo_modifica = :id_gruppo_modifica";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica, PDO::PARAM_INT);
    $delete_stmt->execute();
    exit;
}

// Iniziamo la transazione per assicurare che tutte le modifiche vengano applicate atomicamente
$pdo->beginTransaction();

try {
    // Switch per la gestione della modifica in base alla tabella destinazione
    switch ($tabella_destinazione) {
        case 'nazioni':
            include 'gestisci_nazione.php'; // Include il file che gestisce le modifiche per la tabella "nazioni"
            break;

        case 'tabella_2':
            include 'gestisci_tabella_2.php'; // Aggiungi il file per la gestione della tabella 2
            break;

        // Aggiungi altri casi per le altre tabelle
        default:
            echo "Tabella non supportata.";
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
    // In caso di errore, facciamo il rollback della transazione
    $pdo->rollBack();
    echo "Errore nell'applicare le modifiche: " . $e->getMessage();
}

?>
