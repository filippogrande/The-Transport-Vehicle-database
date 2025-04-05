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

        // Verifica se la nazione esiste
        $check_query = "SELECT * FROM nazione WHERE nome = :id_entita";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
        $check_stmt->execute();
        $nazione = $check_stmt->fetch(PDO::FETCH_ASSOC);

        // Se la nazione esiste, aggiorna
        if ($nazione) {
            // Se il campo modificato è "nome", aggiorniamo il nome della nazione
            if ($campo_modificato == 'nome') {
                // Controlla che il nuovo nome sia unico
                $check_name_query = "SELECT * FROM nazione WHERE nome = :valore_nuovo";
                $check_name_stmt = $pdo->prepare($check_name_query);
                $check_name_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
                $check_name_stmt->execute();

                if ($check_name_stmt->rowCount() > 0) {
                    throw new Exception("Il nome della nazione esiste già.");
                }

                // Esegui l'aggiornamento del nome
                $update_query = "UPDATE nazione SET nome = :valore_nuovo WHERE nome = :id_entita";
                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
                $update_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
                $update_stmt->execute();
            } else {
                // Altri campi, come codice_iso, capitale, ecc.
                switch ($campo_modificato) {
                    case 'codice_iso':
                        $update_query = "UPDATE nazione SET codice_iso = :valore_nuovo WHERE nome = :id_entita";
                        break;
                    case 'codice_iso2':
                        $update_query = "UPDATE nazione SET codice_iso2 = :valore_nuovo WHERE nome = :id_entita";
                        break;
                    case 'continente':
                        $update_query = "UPDATE nazione SET continente = :valore_nuovo WHERE nome = :id_entita";
                        break;
                    case 'capitale':
                        $update_query = "UPDATE nazione SET capitale = :valore_nuovo WHERE nome = :id_entita";
                        break;
                    case 'bandiera':
                        $update_query = "UPDATE nazione SET bandiera = :valore_nuovo WHERE nome = :id_entita";
                        break;
                    default:
                        throw new Exception("Campo non valido.");
                }

                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
                $update_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
                $update_stmt->execute();
            }
        } else {
            // La nazione non esiste, esegui un inserimento
            if (empty($id_entita) || empty($valore_nuovo)) {
                throw new Exception("Impossibile creare una nuova entità con valori vuoti.");
            }

            // Determina la query di inserimento basata sul campo modificato
            switch ($campo_modificato) {
                case 'codice_iso':
                    $insert_query = "INSERT INTO nazione (nome, codice_iso) VALUES (:id_entita, :valore_nuovo)";
                    break;
                case 'codice_iso2':
                    $insert_query = "INSERT INTO nazione (nome, codice_iso2) VALUES (:id_entita, :valore_nuovo)";
                    break;
                case 'continente':
                    $insert_query = "INSERT INTO nazione (nome, continente) VALUES (:id_entita, :valore_nuovo)";
                    break;
                case 'capitale':
                    $insert_query = "INSERT INTO nazione (nome, capitale) VALUES (:id_entita, :valore_nuovo)";
                    break;
                case 'bandiera':
                    $insert_query = "INSERT INTO nazione (nome, bandiera) VALUES (:id_entita, :valore_nuovo)";
                    break;
                default:
                    throw new Exception("Campo non valido.");
            }

            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
            $insert_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
            $insert_stmt->execute();
        }
    }
}

?>
