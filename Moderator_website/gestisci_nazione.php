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
    $nome = null;
    $codice_iso = null;
    $codice_iso2 = null;
    $continente = null;
    $capitale = null;
    $bandiera = null;

    $modifiche_valide = false; // Flag per verificare se ci sono modifiche valide

    // Cicla attraverso tutte le modifiche selezionate
    foreach ($modifiche_selezionate as $id_modifica) {
        echo "<p>Gestione modifica con ID: $id_modifica</p>";

        // Recuperiamo la modifica specifica dal database
        $query = "SELECT * FROM modifiche_in_sospeso WHERE id_modifica = :id_modifica";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_modifica', $id_modifica, PDO::PARAM_INT);
        $stmt->execute();
        $modifica = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($modifica && $modifica['tabella_destinazione'] == 'nazione') {
            $modifiche_valide = true; // Imposta il flag a true
            $id_entita = $modifica['id_entita'];
            $campo_modificato = $modifica['campo_modificato'];
            $valore_nuovo = $modifica['valore_nuovo'];
            $id_gruppo_modifica = $modifica['id_gruppo_modifica'];

            // Accumula i dati delle modifiche nelle variabili
            switch ($campo_modificato) {
                case 'nome':
                    $nome = $valore_nuovo;
                    break;
                case 'codice_iso':
                    $codice_iso = $valore_nuovo;
                    break;
                case 'codice_iso2':
                    $codice_iso2 = $valore_nuovo;
                    break;
                case 'continente':
                    $continente = $valore_nuovo;
                    break;
                case 'capitale':
                    $capitale = $valore_nuovo;
                    break;
                case 'bandiera':
                    $bandiera = $valore_nuovo;
                    break;
                default:
                    echo "<p style='color: red;'>Errore: Campo non valido.</p>";
                    continue;
            }
        } else {
            echo "<p style='color: red;'>Errore: Modifica non valida o tabella destinazione errata.</p>";
            echo "<pre>Dettagli modifica non valida:";
            print_r($modifica); // Mostra i dettagli dell'array $modifica
            echo "</pre>";
        }
    }

    // Verifica se ci sono modifiche valide
    if (!$modifiche_valide) {
        echo "<p style='color: red;'>Errore: Nessuna modifica valida trovata per la tabella 'nazioni'.</p>";
        exit; // Interrompi l'esecuzione
    }

    // Dopo aver accumulato i dati, verifica se la nazione esiste
    if ($nome) {
        $check_query = "SELECT * FROM nazione WHERE nome = :nome";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $check_stmt->execute();
        $nazione = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($nazione) {
            echo "<p>Nazione trovata: {$nazione['nome']}</p>";

            // Traccia i campi modificati
            $campi_modificati = [
                'codice_iso' => $codice_iso !== null,
                'codice_iso2' => $codice_iso2 !== null,
                'continente' => $continente !== null,
                'capitale' => $capitale !== null,
                'bandiera' => $bandiera !== null,
            ];

            // Mantieni i valori esistenti solo per i campi non modificati
            $codice_iso = $campi_modificati['codice_iso'] ? $codice_iso : $nazione['codice_iso'];
            $codice_iso2 = $campi_modificati['codice_iso2'] ? $codice_iso2 : $nazione['codice_iso2'];
            $continente = $campi_modificati['continente'] ? $continente : $nazione['continente'];
            $capitale = $campi_modificati['capitale'] ? $capitale : $nazione['capitale'];
            $bandiera = $campi_modificati['bandiera'] ? $bandiera : $nazione['bandiera'];

            // Aggiorna i dati della nazione
            $update_query = "
                UPDATE nazione 
                SET 
                    codice_iso = :codice_iso,
                    codice_iso2 = :codice_iso2,
                    continente = :continente,
                    capitale = :capitale,
                    bandiera = :bandiera
                WHERE nome = :nome
            ";
            echo "<pre>Query UPDATE: $update_query</pre>";
            echo "<pre>Parametri: nome=$nome, codice_iso=$codice_iso, codice_iso2=$codice_iso2, continente=$continente, capitale=$capitale, bandiera=$bandiera</pre>";

            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':codice_iso', $codice_iso, PDO::PARAM_STR);
            $update_stmt->bindParam(':codice_iso2', $codice_iso2, PDO::PARAM_STR);
            $update_stmt->bindParam(':continente', $continente, PDO::PARAM_STR);
            $update_stmt->bindParam(':capitale', $capitale, PDO::PARAM_STR);
            $update_stmt->bindParam(':bandiera', $bandiera, PDO::PARAM_STR);
            $update_stmt->bindParam(':nome', $nome, PDO::PARAM_STR);

            if ($update_stmt->execute()) {
                echo "<p style='color: green;'>Modifiche applicate con successo alla nazione: $nome.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'aggiornamento: " . implode(", ", $update_stmt->errorInfo()) . "</p>";
            }
        } else {
            echo "<p>Nazione non trovata. Creazione di una nuova entità...</p>";

            // Inserisci una nuova nazione
            $insert_query = "
                INSERT INTO nazione (nome, codice_iso, codice_iso2, continente, capitale, bandiera)
                VALUES (:nome, :codice_iso, :codice_iso2, :continente, :capitale, :bandiera)
            ";
            echo "<pre>Query INSERT: $insert_query</pre>";
            echo "<pre>Parametri: nome=$nome, codice_iso=$codice_iso, codice_iso2=$codice_iso2, continente=$continente, capitale=$capitale, bandiera=$bandiera</pre>";

            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
            $insert_stmt->bindParam(':codice_iso', $codice_iso, PDO::PARAM_STR);
            $insert_stmt->bindParam(':codice_iso2', $codice_iso2, PDO::PARAM_STR);
            $insert_stmt->bindParam(':continente', $continente, PDO::PARAM_STR);
            $insert_stmt->bindParam(':capitale', $capitale, PDO::PARAM_STR);
            $insert_stmt->bindParam(':bandiera', $bandiera, PDO::PARAM_STR);

            if ($insert_stmt->execute()) {
                echo "<p style='color: green;'>Nuova nazione creata con successo: $nome.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'inserimento: " . implode(", ", $insert_stmt->errorInfo()) . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Errore: Nome della nazione non specificato. Impossibile procedere.</p>";
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
