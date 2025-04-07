<?php

// Assicurati che ci sia accesso al database
require_once 'Utilities/dbconnect.php';

// Recupera gli ID delle modifiche direttamente dalla query string
$modifiche_selezionate = $_POST['modifica_selezionata'] ?? [];

if (!is_array($modifiche_selezionate)) {
    $modifiche_selezionate = [$modifiche_selezionate]; // Supporta anche un solo valore
}

echo "<h1>Gestione Modifiche Azienda Costruttrice</h1>";
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
    $short_desc = null;
    $long_desc = null;
    $fondazione = null;
    $chiusura = null;
    $sede = null;
    $nazione = null;
    $sito_web = null;
    $stato = null;
    $logo = null;

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

        if ($modifica && $modifica['tabella_destinazione'] == 'azienda_costruttrice') {
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
                case 'short_desc':
                    $short_desc = $valore_nuovo;
                    break;
                case 'long_desc':
                    $long_desc = $valore_nuovo;
                    break;
                case 'fondazione':
                    $fondazione = $valore_nuovo;
                    break;
                case 'chiusura':
                    $chiusura = $valore_nuovo;
                    break;
                case 'sede':
                    $sede = $valore_nuovo;
                    break;
                case 'nazione':
                    $nazione = $valore_nuovo;
                    break;
                case 'sito_web':
                    $sito_web = $valore_nuovo;
                    break;
                case 'stato':
                    $stato = $valore_nuovo;
                    break;
                case 'logo':
                    $logo = $valore_nuovo;
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
        echo "<p style='color: red;'>Errore: Nessuna modifica valida trovata per la tabella 'azienda_costruttrice'.</p>";
        exit; // Interrompi l'esecuzione
    }

    // Dopo aver accumulato i dati, verifica se l'azienda esiste
    if ($nome) {
        $check_query = "SELECT * FROM azienda_costruttrice WHERE nome = :nome";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $check_stmt->execute();
        $azienda = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($azienda) {
            echo "<p>Azienda trovata: {$azienda['nome']}</p>";

            // Aggiorna i dati dell'azienda
            $update_query = "
                UPDATE azienda_costruttrice 
                SET 
                    short_desc = :short_desc,
                    long_desc = :long_desc,
                    fondazione = :fondazione,
                    chiusura = :chiusura,
                    sede = :sede,
                    nazione = :nazione,
                    sito_web = :sito_web,
                    stato = :stato,
                    logo = :logo
                WHERE nome = :nome
            ";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':short_desc', $short_desc, PDO::PARAM_STR);
            $update_stmt->bindParam(':long_desc', $long_desc, PDO::PARAM_STR);
            $update_stmt->bindParam(':fondazione', $fondazione, PDO::PARAM_STR);
            $update_stmt->bindParam(':chiusura', $chiusura, PDO::PARAM_STR);
            $update_stmt->bindParam(':sede', $sede, PDO::PARAM_STR);
            $update_stmt->bindParam(':nazione', $nazione, PDO::PARAM_STR);
            $update_stmt->bindParam(':sito_web', $sito_web, PDO::PARAM_STR);
            $update_stmt->bindParam(':stato', $stato, PDO::PARAM_STR);
            $update_stmt->bindParam(':logo', $logo, PDO::PARAM_STR);
            $update_stmt->bindParam(':nome', $nome, PDO::PARAM_STR);

            if ($update_stmt->execute()) {
                echo "<p style='color: green;'>Modifiche applicate con successo all'azienda: $nome.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'aggiornamento: " . implode(", ", $update_stmt->errorInfo()) . "</p>";
            }
        } else {
            echo "<p>Azienda non trovata. Creazione di una nuova entità...</p>";

            // Inserisci una nuova azienda
            $insert_query = "
                INSERT INTO azienda_costruttrice (nome, short_desc, long_desc, fondazione, chiusura, sede, nazione, sito_web, stato, logo)
                VALUES (:nome, :short_desc, :long_desc, :fondazione, :chiusura, :sede, :nazione, :sito_web, :stato, :logo)
            ";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
            $insert_stmt->bindParam(':short_desc', $short_desc, PDO::PARAM_STR);
            $insert_stmt->bindParam(':long_desc', $long_desc, PDO::PARAM_STR);
            $insert_stmt->bindParam(':fondazione', $fondazione, PDO::PARAM_STR);
            $insert_stmt->bindParam(':chiusura', $chiusura, PDO::PARAM_STR);
            $insert_stmt->bindParam(':sede', $sede, PDO::PARAM_STR);
            $insert_stmt->bindParam(':nazione', $nazione, PDO::PARAM_STR);
            $insert_stmt->bindParam(':sito_web', $sito_web, PDO::PARAM_STR);
            $insert_stmt->bindParam(':stato', $stato, PDO::PARAM_STR);
            $insert_stmt->bindParam(':logo', $logo, PDO::PARAM_STR);

            if ($insert_stmt->execute()) {
                echo "<p style='color: green;'>Nuova azienda creata con successo: $nome.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'inserimento: " . implode(", ", $insert_stmt->errorInfo()) . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Errore: Nome dell'azienda non specificato. Impossibile procedere.</p>";
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
