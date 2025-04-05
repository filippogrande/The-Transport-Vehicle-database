<?php

// Assicurati che ci sia accesso al database
require_once 'Utilities/dbconnect.php';

// Recupera gli ID delle modifiche direttamente dalla query string
$modifiche_selezionate = $_GET['modifica_selezionata'] ?? [];

if (!is_array($modifiche_selezionate)) {
    $modifiche_selezionate = [$modifiche_selezionate]; // Supporta anche un solo valore
}

// Cicla attraverso tutte le modifiche selezionate
foreach ($modifiche_selezionate as $id_modifica) {
    // Recuperiamo la modifica specifica dal database
    $query = "SELECT * FROM modifiche_in_sospeso WHERE id_modifica = :id_modifica";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_modifica', $id_modifica, PDO::PARAM_INT);
    $stmt->execute();
    $modifica = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($modifica && $modifica['tabella_destinazione'] == 'nazioni') {
        $id_entita = $modifica['id_entita'];
        $campo_modificato = $modifica['campo_modificato'];
        $valore_nuovo = $modifica['valore_nuovo'];

        // Controlla se la nazione esiste
        $check_query = "SELECT * FROM nazione WHERE nome = :id_entita";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            // Aggiorna il campo specifico
            $update_query = "UPDATE nazione SET $campo_modificato = :valore_nuovo WHERE nome = :id_entita";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
            $update_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
            $update_stmt->execute();
        } else {
            if (empty($valore_nuovo)) {
                throw new Exception("Impossibile creare una nuova entità con campi nulli.");
            }

            $insert_query = "INSERT INTO nazione (nome, $campo_modificato) VALUES (:id_entita, :valore_nuovo)";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
            $insert_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
            $insert_stmt->execute();
        }
    }
}

?>
