<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

// Recupera gli ID delle modifiche selezionate
$modifiche_selezionate = $_POST['modifica_selezionata'] ?? [];

if (!is_array($modifiche_selezionate)) {
    $modifiche_selezionate = [$modifiche_selezionate]; // Supporta anche un solo valore
}

echo "<h1>Gestione Modifiche Veicolo</h1>";
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

    // Aggiungi il pulsante "Torna alla Home"
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

try {
    // Variabili per accumulare i dati delle modifiche
    $id_modello = null;
    $anno_produzione = null;
    $numero_targa = null;
    $descrizione = null;
    $stato_veicolo = null;

    $modifiche_valide = false; // Flag per verificare se ci sono modifiche valide

    // Campi validi per la tabella veicolo
    $campi_validi = ['id_modello', 'anno_produzione', 'numero_targa', 'descrizione', 'stato_veicolo'];

    // Cicla attraverso tutte le modifiche selezionate
    foreach ($modifiche_selezionate as $id_modifica) {
        echo "<p>Gestione modifica con ID: $id_modifica</p>";

        // Recuperiamo la modifica specifica dal database
        $query = "SELECT * FROM modifiche_in_sospeso WHERE id_modifica = :id_modifica";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_modifica', $id_modifica, PDO::PARAM_INT);
        $stmt->execute();
        $modifica = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($modifica && $modifica['tabella_destinazione'] == 'veicolo') {
            $campo_modificato = $modifica['campo_modificato'];
            $valore_nuovo = $modifica['valore_nuovo'];

            if (!in_array($campo_modificato, $campi_validi)) {
                echo "<p style='color: red;'>Errore: Campo non valido ($campo_modificato).</p>";
                continue;
            }

            $modifiche_valide = true; // Imposta il flag a true
            $id_gruppo_modifica = $modifica['id_gruppo_modifica'];

            // Accumula i dati delle modifiche nelle variabili
            switch ($campo_modificato) {
                case 'id_modello':
                    $id_modello = !empty($valore_nuovo) ? (int)$valore_nuovo : null;
                    break;
                case 'anno_produzione':
                    $anno_produzione = !empty($valore_nuovo) ? (int)$valore_nuovo : null;
                    break;
                case 'numero_targa':
                    $numero_targa = !empty($valore_nuovo) ? $valore_nuovo : null;
                    break;
                case 'descrizione':
                    $descrizione = !empty($valore_nuovo) ? $valore_nuovo : null;
                    break;
                case 'stato_veicolo':
                    $stato_veicolo = !empty($valore_nuovo) ? $valore_nuovo : null;
                    break;
            }
        } else {
            echo "<p style='color: red;'>Errore: Modifica non valida o tabella destinazione errata.</p>";
        }
    }

    // Verifica se ci sono modifiche valide
    if (!$modifiche_valide) {
        echo "<p style='color: red;'>Errore: Nessuna modifica valida trovata per la tabella 'veicolo'.</p>";
        exit; // Interrompi l'esecuzione
    }

    // Inserisci o aggiorna il veicolo nel database
    if ($numero_targa) {
        // Verifica se il veicolo esiste già
        $check_query = "SELECT * FROM veicolo WHERE numero_targa = :numero_targa";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':numero_targa', $numero_targa, PDO::PARAM_STR);
        $check_stmt->execute();
        $veicolo = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($veicolo) {
            echo "<p>Veicolo trovato: {$veicolo['numero_targa']}</p>";

            // Aggiorna i dati del veicolo
            $update_query = "
                UPDATE veicolo 
                SET 
                    id_modello = :id_modello,
                    anno_produzione = :anno_produzione,
                    descrizione = :descrizione,
                    stato_veicolo = :stato_veicolo
                WHERE numero_targa = :numero_targa
            ";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':id_modello', $id_modello, PDO::PARAM_INT);
            $update_stmt->bindParam(':anno_produzione', $anno_produzione, PDO::PARAM_INT);
            $update_stmt->bindParam(':descrizione', $descrizione, PDO::PARAM_STR);
            $update_stmt->bindParam(':stato_veicolo', $stato_veicolo, PDO::PARAM_STR);
            $update_stmt->bindParam(':numero_targa', $numero_targa, PDO::PARAM_STR);

            if ($update_stmt->execute()) {
                echo "<p style='color: green;'>Modifiche applicate con successo al veicolo: $numero_targa.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'aggiornamento: " . implode(", ", $update_stmt->errorInfo()) . "</p>";
            }
        } else {
            echo "<p>Veicolo non trovato. Creazione di un nuovo veicolo...</p>";

            // Inserisci un nuovo veicolo
            $insert_query = "
                INSERT INTO veicolo (id_modello, anno_produzione, numero_targa, descrizione, stato_veicolo)
                VALUES (:id_modello, :anno_produzione, :numero_targa, :descrizione, :stato_veicolo)
            ";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':id_modello', $id_modello, PDO::PARAM_INT);
            $insert_stmt->bindParam(':anno_produzione', $anno_produzione, PDO::PARAM_INT);
            $insert_stmt->bindParam(':numero_targa', $numero_targa, PDO::PARAM_STR);
            $insert_stmt->bindParam(':descrizione', $descrizione, PDO::PARAM_STR);
            $insert_stmt->bindParam(':stato_veicolo', $stato_veicolo, PDO::PARAM_STR);

            if ($insert_stmt->execute()) {
                echo "<p style='color: green;'>Nuovo veicolo creato con successo: $numero_targa.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'inserimento: " . implode(", ", $insert_stmt->errorInfo()) . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Errore: Numero di targa non specificato. Impossibile procedere.</p>";
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
