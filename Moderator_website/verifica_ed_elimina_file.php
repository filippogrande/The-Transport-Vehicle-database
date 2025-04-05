<?php
require_once 'Utilities/dbconnect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$modifiche_non_selezionate = $data['modifiche_non_selezionate'] ?? [];

if (empty($modifiche_non_selezionate)) {
    echo json_encode(['success' => true, 'message' => 'Nessuna modifica non selezionata.']);
    exit;
}

// Funzione per verificare se un valore rappresenta un file
function isFile($value) {
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $video_extensions = ['mp4', 'webm', 'ogg'];
    $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));

    return in_array($ext, $image_extensions) || in_array($ext, $video_extensions);
}

try {
    // Recupera i valori di campo_modificato per le modifiche non selezionate
    $placeholders = implode(',', array_fill(0, count($modifiche_non_selezionate), '?'));
    $query = "SELECT valore_nuovo FROM modifiche_in_sospeso WHERE id_modifica IN ($placeholders)";
    $stmt = $pdo->prepare($query);
    $stmt->execute($modifiche_non_selezionate);
    $values = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Filtra i valori che rappresentano file
    $files = array_filter($values, 'isFile');

    // Elimina i file dal disco
    foreach ($files as $file) {
        if (file_exists($file)) {
            if (!unlink($file)) {
                throw new Exception("Errore durante l'eliminazione del file: $file");
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'File eliminati con successo.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
