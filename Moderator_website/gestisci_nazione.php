<?php

// Assicurati che ci sia accesso al database
require_once 'Utilities/dbconnect.php';

// Recupera gli ID delle modifiche direttamente dalla query string
$modifiche_selezionate = $_POST['modifica_selezionata'] ?? [];

if (!is_array($modifiche_selezionate)) {
    $modifiche_selezionate = [$modifiche_selezionate]; // Supporta anche un solo valore
}

echo "<h1>Gestione Modifiche Nazione</h1>";
echo "<p>Numero di modifiche selezionate: " . count($modifiche_selezionate) . "</p>";

try {
    // Cicla attraverso tutte le modifiche selezionate
    foreach ($modifiche_selezionate as $id_modifica) {
        echo "<p>Gestione modifica con ID: $id_modifica</p>";

        // Recuperiamo la modifica specifica dal database
        $query = "SELECT * FROM modifiche_in_sospeso WHERE id_modifica = :id_modifica";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_modifica', $id_modifica, PDO::PARAM_INT);
        $stmt->execute();
        $modifica = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debug: Mostra i dati della modifica recuperata
        echo "<pre>Dati modifica recuperati: ";
        print_r($modifica);
        echo "</pre>";

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

            if ($nazione) {
                echo "<p>Nazione trovata: {$nazione['nome']}</p>";

                // Aggiorna il campo modificato
                if ($campo_modificato == 'nome') {
                    $check_name_query = "SELECT * FROM nazione WHERE nome = :valore_nuovo";
                    $check_name_stmt = $pdo->prepare($check_name_query);
                    $check_name_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
                    $check_name_stmt->execute();

                    if ($check_name_stmt->rowCount() > 0) {
                        echo "<p style='color: red;'>Errore: Il nome della nazione esiste già.</p>";
                        continue;
                    }

                    $update_query = "UPDATE nazione SET nome = :valore_nuovo WHERE nome = :id_entita";
                } else {
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
                            echo "<p style='color: red;'>Errore: Campo non valido.</p>";
                            continue;
                    }
                }

                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
                $update_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
                if ($update_stmt->execute()) {
                    echo "<p style='color: green;'>Modifica applicata con successo: $campo_modificato aggiornato a $valore_nuovo.</p>";
                } else {
                    echo "<p style='color: red;'>Errore nell'aggiornamento: " . implode(", ", $update_stmt->errorInfo()) . "</p>";
                }
            } else {
                echo "<p>Nazione non trovata. Creazione di una nuova entità...</p>";

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
                        echo "<p style='color: red;'>Errore: Campo non valido.</p>";
                        continue;
                }

                $insert_stmt = $pdo->prepare($insert_query);
                $insert_stmt->bindParam(':id_entita', $id_entita, PDO::PARAM_STR);
                $insert_stmt->bindParam(':valore_nuovo', $valore_nuovo, PDO::PARAM_STR);
                if ($insert_stmt->execute()) {
                    echo "<p style='color: green;'>Nuova entità creata con successo: $campo_modificato impostato a $valore_nuovo.</p>";
                } else {
                    echo "<p style='color: red;'>Errore nell'inserimento: " . implode(", ", $insert_stmt->errorInfo()) . "</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>Errore: Modifica non valida o tabella destinazione errata.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Errore: " . $e->getMessage() . "</p>";
}

?>
