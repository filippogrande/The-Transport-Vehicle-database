<?php

// Cicla attraverso tutte le modifiche selezionate
foreach ($modifiche_selezionate as $id_modifica) {
    // Recuperiamo la modifica specifica dal database
    $query = "SELECT * FROM modifiche_in_sospeso WHERE id_modifica = :id_modifica";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_modifica', $id_modifica, PDO::PARAM_INT);
    $stmt->execute();
    $modifica = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($modifica && $modifica['tabella_destinazione'] == 'nazioni') {
        // Recupera il nome della nazione (id_entita) e il campo da modificare
        $id_entita = $modifica['id_entita']; // E.g., "Italia"
        $campo_modificato = $modifica['campo_modificato']; // E.g., "bandiera"
        $valore_nuovo = $modifica['valore_nuovo'];

        // Controlla se la nazione esiste già nel database
        $check_query = "SELECT * FROM nazione WHERE nome = :id_entita";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
        $check_stmt->execute();

        // Se la nazione esiste, aggiorniamo il campo specifico
        if ($check_stmt->rowCount() > 0) {
            // Entità esistente, aggiorna il campo specifico
            $update_query = "UPDATE nazione SET $campo_modificato = :valore_nuovo WHERE nome = :id_entita";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
            $update_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
            $update_stmt->execute();
        } else {
            // Entità non esistente, creiamo una nuova nazione
            // Verifica che tutti i campi obbligatori siano presenti
            if (empty($modifica['valore_nuovo'])) {
                throw new Exception("Impossibile creare una nuova entità con campi nulli.");
            }

            // Se non esiste, creiamo una nuova nazione
            $insert_query = "INSERT INTO nazione (nome, $campo_modificato) VALUES (:id_entita, :valore_nuovo)";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
            $insert_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
            $insert_stmt->execute();
        }
    }
}

?>
