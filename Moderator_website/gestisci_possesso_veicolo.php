<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

// Recupera gli ID delle modifiche selezionate
$modifiche_selezionate = $_POST['modifica_selezionata'] ?? [];

if (!is_array($modifiche_selezionate)) {
    $modifiche_selezionate = [$modifiche_selezionate]; // Supporta anche un solo valore
}

echo "<h1>Gestione Modifiche Possesso Veicolo</h1>";
echo "<p>Numero di modifiche selezionate: " . count($modifiche_selezionate) . "</p>";

if (empty($modifiche_selezionate)) {
    echo "<p style='color: red;'>Nessuna modifica selezionata. Procedo con l'eliminazione del gruppo di modifiche.</p>";

    // Recupera l'id_gruppo_modifica dalla richiesta
    $id_gruppo_modifica = $_POST['id_gruppo_modifica'] ?? null;

    if ($id_gruppo_modifica) {
        eliminaModifiche($id_gruppo_modifica);
        echo "<p style='color: green;'>Modifiche del gruppo ID $id_gruppo_modifica eliminate con successo.</p>";
    } else {
        echo "<p style='color: red;'>Errore: ID gruppo modifica non fornito. Impossibile procedere.</p>";
    }

    echo '<div class="mt-4">';
    echo '<a href="index.php" class="btn btn-primary">Torna alla Home</a>';
    echo '</div>';

    exit; // Interrompi l'esecuzione
}

function eliminaModifiche($id_gruppo_modifica) {
    require 'Utilities/dbconnect.php'; // Assicurati che la connessione al database sia disponibile

    try {
        $query = "DELETE FROM modifiche_in_sospeso WHERE id_gruppo_modifica = :id_gruppo_modifica";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_gruppo_modifica', $id_gruppo_modifica, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Modifiche del gruppo ID $id_gruppo_modifica eliminate con successo.</p>";
        } else {
            echo "<p style='color: red;'>Errore durante l'eliminazione delle modifiche del gruppo ID $id_gruppo_modifica.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Errore durante l'eliminazione delle modifiche: " . $e->getMessage() . "</p>";
    }
}

function eliminaPossesso($id_veicolo, $id_azienda_operatrice) {
    require 'Utilities/dbconnect.php'; // Assicurati che la connessione al database sia disponibile

    try {
        $query = "
            DELETE FROM possesso_veicolo 
            WHERE id_veicolo = :id_veicolo AND id_azienda_operatrice = :id_azienda_operatrice
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_veicolo', $id_veicolo, PDO::PARAM_INT);
        $stmt->bindParam(':id_azienda_operatrice', $id_azienda_operatrice, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Possesso veicolo eliminato con successo per il veicolo ID: $id_veicolo e azienda ID: $id_azienda_operatrice.</p>";
        } else {
            echo "<p style='color: red;'>Errore nell'eliminazione del possesso: " . implode(", ", $stmt->errorInfo()) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Errore durante l'eliminazione del possesso: " . $e->getMessage() . "</p>";
    }
}

try {
    // Variabili per accumulare i dati delle modifiche
    $id_veicolo = null;
    $id_azienda_operatrice = null;
    $data_inizio_possesso = null;
    $data_fine_possesso = null;
    $stato_veicolo_azienda = null;

    $modifiche_valide = false; // Flag per verificare se ci sono modifiche valide

    // Campi validi per la tabella possesso_veicolo
    $campi_validi = ['id_veicolo', 'id_azienda_operatrice', 'data_inizio_possesso', 'data_fine_possesso', 'stato_veicolo_azienda'];

    // Cicla attraverso tutte le modifiche selezionate
    foreach ($modifiche_selezionate as $id_modifica) {
        echo "<p>Gestione modifica con ID: $id_modifica</p>";

        // Recuperiamo la modifica specifica dal database
        $query = "SELECT * FROM modifiche_in_sospeso WHERE id_modifica = :id_modifica";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_modifica', $id_modifica, PDO::PARAM_INT);
        $stmt->execute();
        $modifica = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($modifica && $modifica['tabella_destinazione'] == 'possesso_veicolo') {
            $campo_modificato = $modifica['campo_modificato'];
            $valore_nuovo = $modifica['valore_nuovo'];
            $valore_vecchio = $modifica['valore_vecchio'];

            if (!in_array($campo_modificato, $campi_validi)) {
                echo "<p style='color: red;'>Errore: Campo non valido ($campo_modificato).</p>";
                continue;
            }

            $modifiche_valide = true; // Imposta il flag a true
            $id_gruppo_modifica = $modifica['id_gruppo_modifica'];

            // Accumula i dati delle modifiche nelle variabili
            switch ($campo_modificato) {
                case 'id_veicolo':
                    $id_veicolo = $valore_nuovo;
                    $id_veicolo_vecchio = $valore_vecchio;
                    break;
                case 'id_azienda_operatrice':
                    $id_azienda_operatrice = $valore_nuovo;
                    $id_azienda_operatrice_vecchia = $valore_vecchio;
                    break;
                case 'data_inizio_possesso':
                    $data_inizio_possesso = $valore_nuovo;
                    break;
                case 'data_fine_possesso':
                    $data_fine_possesso = $valore_nuovo;
                    break;
                case 'stato_veicolo_azienda':
                    $stato_veicolo_azienda = $valore_nuovo;
                    break;
            }
        } else {
            echo "<p style='color: red;'>Errore: Modifica non valida o tabella destinazione errata.</p>";
        }
    }

    // Verifica se ci sono modifiche valide
    if (!$modifiche_valide) {
        echo "<p style='color: red;'>Errore: Nessuna modifica valida trovata per la tabella 'possesso_veicolo'.</p>";
        exit; // Interrompi l'esecuzione
    }

    // Elimina l'entry se sia id_veicolo che id_azienda_operatrice hanno un nuovo valore null
    if ($id_veicolo === null && $id_azienda_operatrice === null) {
        eliminaPossesso($id_veicolo_vecchio, $id_azienda_operatrice_vecchia);
        eliminaModifiche($id_gruppo_modifica);
    } else {
        // Inserisci o aggiorna il possesso veicolo nel database
        if ($id_veicolo && $id_azienda_operatrice) {
            $insert_query = "
                INSERT INTO possesso_veicolo (id_veicolo, id_azienda_operatrice, data_inizio_possesso, data_fine_possesso, stato_veicolo_azienda)
                VALUES (:id_veicolo, :id_azienda_operatrice, :data_inizio_possesso, :data_fine_possesso, :stato_veicolo_azienda)
            ";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':id_veicolo', $id_veicolo, PDO::PARAM_INT);
            $insert_stmt->bindParam(':id_azienda_operatrice', $id_azienda_operatrice, PDO::PARAM_INT);
            $insert_stmt->bindParam(':data_inizio_possesso', $data_inizio_possesso);
            $insert_stmt->bindParam(':data_fine_possesso', $data_fine_possesso);
            $insert_stmt->bindParam(':stato_veicolo_azienda', $stato_veicolo_azienda);

            if ($insert_stmt->execute()) {
                echo "<p style='color: green;'>Nuovo possesso veicolo creato con successo per il veicolo ID: $id_veicolo.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'inserimento: " . implode(", ", $insert_stmt->errorInfo()) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Errore: ID del veicolo o dell'azienda operatrice non specificato. Impossibile procedere.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Errore: " . $e->getMessage() . "</p>";
}
?>

<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary">Torna all'Index</a>
</div>
</body>
</html>
