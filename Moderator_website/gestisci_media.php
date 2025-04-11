<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

// Recupera gli ID delle modifiche selezionate
$modifiche_selezionate = $_POST['modifica_selezionata'] ?? [];

if (!is_array($modifiche_selezionate)) {
    $modifiche_selezionate = [$modifiche_selezionate]; // Supporta anche un solo valore
}

echo "<h1>Gestione Modifiche Media</h1>";
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

try {
    // Variabili per accumulare i dati delle modifiche
    $tipo_media = null;
    $url_media = null;
    $descrizione = null;
    $copyright = null;
    $licenza = null;

    $modifiche_valide = false; // Flag per verificare se ci sono modifiche valide

    // Campi validi per la tabella media
    $campi_validi = ['tipo_media', 'url_media', 'descrizione', 'copyright', 'licenza'];

    // Cicla attraverso tutte le modifiche selezionate
    foreach ($modifiche_selezionate as $id_modifica) {
        echo "<p>Gestione modifica con ID: $id_modifica</p>";

        // Recuperiamo la modifica specifica dal database
        $query = "SELECT * FROM modifiche_in_sospeso WHERE id_modifica = :id_modifica";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_modifica', $id_modifica, PDO::PARAM_INT);
        $stmt->execute();
        $modifica = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($modifica && $modifica['tabella_destinazione'] == 'media') {
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
                case 'tipo_media':
                    $tipo_media = $valore_nuovo;
                    break;
                case 'url_media':
                    $url_media = $valore_nuovo;
                    break;
                case 'descrizione':
                    $descrizione = $valore_nuovo;
                    break;
                case 'copyright':
                    $copyright = $valore_nuovo;
                    break;
                case 'licenza':
                    $licenza = $valore_nuovo;
                    break;
            }
        } else {
            echo "<p style='color: red;'>Errore: Modifica non valida o tabella destinazione errata.</p>";
        }
    }

    // Verifica se ci sono modifiche valide
    if (!$modifiche_valide) {
        echo "<p style='color: red;'>Errore: Nessuna modifica valida trovata per la tabella 'media'.</p>";
        exit; // Interrompi l'esecuzione
    }

    // Inserisci o aggiorna il media nel database
    if ($url_media) {
        // Verifica se il media esiste già
        $check_query = "SELECT * FROM media WHERE url_media = :url_media";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':url_media', $url_media, PDO::PARAM_STR);
        $check_stmt->execute();
        $media = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($media) {
            echo "<p>Media trovato: {$media['url_media']}</p>";

            // Aggiorna i dati del media
            $update_query = "
                UPDATE media 
                SET 
                    tipo_media = :tipo_media,
                    descrizione = :descrizione,
                    copyright = :copyright,
                    licenza = :licenza
                WHERE url_media = :url_media
            ";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':tipo_media', $tipo_media);
            $update_stmt->bindParam(':descrizione', $descrizione);
            $update_stmt->bindParam(':copyright', $copyright);
            $update_stmt->bindParam(':licenza', $licenza);
            $update_stmt->bindParam(':url_media', $url_media);

            if ($update_stmt->execute()) {
                echo "<p style='color: green;'>Modifiche applicate con successo al media: $url_media.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'aggiornamento: " . implode(", ", $update_stmt->errorInfo()) . "</p>";
            }
        } else {
            echo "<p>Media non trovato. Creazione di un nuovo media...</p>";

            // Inserisci un nuovo media
            $insert_query = "
                INSERT INTO media (tipo_media, url_media, descrizione, copyright, licenza)
                VALUES (:tipo_media, :url_media, :descrizione, :copyright, :licenza)
            ";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':tipo_media', $tipo_media);
            $insert_stmt->bindParam(':url_media', $url_media);
            $insert_stmt->bindParam(':descrizione', $descrizione);
            $insert_stmt->bindParam(':copyright', $copyright);
            $insert_stmt->bindParam(':licenza', $licenza);

            if ($insert_stmt->execute()) {
                echo "<p style='color: green;'>Nuovo media creato con successo: $url_media.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'inserimento: " . implode(", ", $insert_stmt->errorInfo()) . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Errore: URL del media non specificato. Impossibile procedere.</p>";
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
