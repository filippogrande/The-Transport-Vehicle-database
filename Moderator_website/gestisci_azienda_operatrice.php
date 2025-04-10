<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Utilities/dbconnect.php'; // Connessione al database con PDO

// Recupera gli ID delle modifiche direttamente dalla query string
$modifiche_selezionate = $_POST['modifica_selezionata'] ?? [];

if (!is_array($modifiche_selezionate)) {
    $modifiche_selezionate = [$modifiche_selezionate]; // Supporta anche un solo valore
}

echo "<h1>Gestione Modifiche Azienda Operatrice</h1>";
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
    $nome_azienda = null;
    $nome_precedente = null;
    $sede_legale = null;
    $città = null;
    $paese = null;
    $numero_telefono = null;
    $email = null;
    $data_inizio_attività = null;
    $descrizione = null;
    $foto_logo = null;
    $stato_azienda = null;

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

        if ($modifica && $modifica['tabella_destinazione'] == 'azienda_operatrice') {
            $modifiche_valide = true; // Imposta il flag a true
            $id_entita = $modifica['id_entita'];
            $campo_modificato = $modifica['campo_modificato'];
            $valore_nuovo = $modifica['valore_nuovo'];
            $id_gruppo_modifica = $modifica['id_gruppo_modifica'];

            // Accumula i dati delle modifiche nelle variabili
            switch ($campo_modificato) {
                case 'nome_azienda':
                    $nome_azienda = $valore_nuovo;
                    break;
                case 'nome_precedente':
                    $nome_precedente = $valore_nuovo;
                    break;
                case 'sede_legale':
                    $sede_legale = $valore_nuovo;
                    break;
                case 'città':
                    $città = $valore_nuovo;
                    break;
                case 'paese':
                    $paese = $valore_nuovo;
                    break;
                case 'numero_telefono':
                    $numero_telefono = $valore_nuovo;
                    break;
                case 'email':
                    $email = $valore_nuovo;
                    break;
                case 'data_inizio_attività':
                    $data_inizio_attività = $valore_nuovo;
                    break;
                case 'descrizione':
                    $descrizione = $valore_nuovo;
                    break;
                case 'foto_logo':
                    $foto_logo = $valore_nuovo;
                    break;
                case 'stato_azienda':
                    $stato_azienda = $valore_nuovo;
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
        echo "<p style='color: red;'>Errore: Nessuna modifica valida trovata per la tabella 'azienda_operatrice'.</p>";
        exit; // Interrompi l'esecuzione
    }

    // Dopo aver accumulato i dati, verifica se l'azienda esiste
    if ($nome_azienda) {
        $check_query = "SELECT * FROM azienda_operatrice WHERE nome_azienda = :nome_azienda";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':nome_azienda', $nome_azienda, PDO::PARAM_STR);
        $check_stmt->execute();
        $azienda = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($azienda) {
            echo "<p>Azienda trovata: {$azienda['nome_azienda']}</p>";

            // Traccia i campi modificati
            $campi_modificati = [
                'nome_precedente' => $nome_precedente !== null,
                'sede_legale' => $sede_legale !== null,
                'città' => $città !== null,
                'paese' => $paese !== null,
                'numero_telefono' => $numero_telefono !== null,
                'email' => $email !== null,
                'data_inizio_attività' => $data_inizio_attività !== null,
                'descrizione' => $descrizione !== null,
                'foto_logo' => $foto_logo !== null,
                'stato_azienda' => $stato_azienda !== null,
            ];

            // Mantieni i valori esistenti solo per i campi non modificati
            $nome_precedente = $campi_modificati['nome_precedente'] ? $nome_precedente : $azienda['nome_precedente'];
            $sede_legale = $campi_modificati['sede_legale'] ? $sede_legale : $azienda['sede_legale'];
            $città = $campi_modificati['città'] ? $città : $azienda['città'];
            $paese = $campi_modificati['paese'] ? $paese : $azienda['paese'];
            $numero_telefono = $campi_modificati['numero_telefono'] ? $numero_telefono : $azienda['numero_telefono'];
            $email = $campi_modificati['email'] ? $email : $azienda['email'];
            $data_inizio_attività = $campi_modificati['data_inizio_attività'] ? $data_inizio_attività : $azienda['data_inizio_attività'];
            $descrizione = $campi_modificati['descrizione'] ? $descrizione : $azienda['descrizione'];
            $foto_logo = $campi_modificati['foto_logo'] ? $foto_logo : $azienda['foto_logo'];
            $stato_azienda = $campi_modificati['stato_azienda'] ? $stato_azienda : $azienda['stato_azienda'];

            // Aggiorna i dati dell'azienda
            $update_query = "
                UPDATE azienda_operatrice 
                SET 
                    nome_precedente = :nome_precedente,
                    sede_legale = :sede_legale,
                    città = :città,
                    paese = :paese,
                    numero_telefono = :numero_telefono,
                    email = :email,
                    data_inizio_attività = :data_inizio_attività,
                    descrizione = :descrizione,
                    foto_logo = :foto_logo,
                    stato_azienda = :stato_azienda
                WHERE nome_azienda = :nome_azienda
            ";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':nome_precedente', $nome_precedente);
            $update_stmt->bindParam(':sede_legale', $sede_legale);
            $update_stmt->bindParam(':città', $città);
            $update_stmt->bindParam(':paese', $paese);
            $update_stmt->bindParam(':numero_telefono', $numero_telefono);
            $update_stmt->bindParam(':email', $email);
            $update_stmt->bindParam(':data_inizio_attività', $data_inizio_attività);
            $update_stmt->bindParam(':descrizione', $descrizione);
            $update_stmt->bindParam(':foto_logo', $foto_logo);
            $update_stmt->bindParam(':stato_azienda', $stato_azienda);
            $update_stmt->bindParam(':nome_azienda', $nome_azienda);

            if ($update_stmt->execute()) {
                echo "<p style='color: green;'>Modifiche applicate con successo all'azienda: $nome_azienda.</p>";
                eliminaModifiche($id_gruppo_modifica);
            } else {
                echo "<p style='color: red;'>Errore nell'aggiornamento: " . implode(", ", $update_stmt->errorInfo()) . "</p>";
            }
        } else {
            echo "<p>Azienda non trovata. Creazione di una nuova entità...</p>";

            // Inserisci una nuova azienda
            $insert_query = "
                INSERT INTO azienda_operatrice (nome_azienda, nome_precedente, sede_legale, città, paese, numero_telefono, email, data_inizio_attività, descrizione, foto_logo, stato_azienda)
                VALUES (:nome_azienda, :nome_precedente, :sede_legale, :città, :paese, :numero_telefono, :email, :data_inizio_attività, :descrizione, :foto_logo, :stato_azienda)
            ";
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_stmt->bindParam(':nome_azienda', $nome_azienda);
            $insert_stmt->bindParam(':nome_precedente', $nome_precedente);
            $insert_stmt->bindParam(':sede_legale', $sede_legale);
            $insert_stmt->bindParam(':città', $città);
            $insert_stmt->bindParam(':paese', $paese);
            $insert_stmt->bindParam(':numero_telefono', $numero_telefono);
            $insert_stmt->bindParam(':email', $email);
            $insert_stmt->bindParam(':data_inizio_attività', $data_inizio_attività);
            $insert_stmt->bindParam(':descrizione', $descrizione);
            $insert_stmt->bindParam(':foto_logo', $foto_logo);
            $insert_stmt->bindParam(':stato_azienda', $stato_azienda);

            if ($insert_stmt->execute()) {
                echo "<p style='color: green;'>Nuova azienda creata con successo: $nome_azienda.</p>";
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
