<?php

require_once 'Utilities/dbconnect.php';

// Ricevi i dati dal form
$id_gruppo_modifica = $_POST['id_gruppo_modifica'];
$tabella_destinazione = $_POST['tabella_destinazione'];

switch ($tabella_destinazione) {
    case 'tabella_1':
        include 'gestisci_tabella_1.php';
        break;
    case 'tabella_2':
        include 'gestisci_tabella_2.php';
        break;
    // Aggiungi altri casi per le varie tabelle
    default:
        echo "Tabella non supportata.";
        exit;
}

// Gestisci i dati ricevuti e applica la modifica
// Puoi accedere ai campi come $_POST['campo_modificato'] per ottenere il valore on/off

?>
