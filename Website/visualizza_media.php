<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Correggi il percorso per includere correttamente dbconnect.php
require_once __DIR__ . '/Utilities/dbconnect.php'; // Connessione al database con PDO

include '/header.html'; // Include l'header

try {
    // Recupera tutti i media dalla tabella `media`
    $query = "SELECT * FROM media ORDER BY data_caricamento DESC";
    $stmt = $pdo->query($query);
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei media: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Media</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="mb-3">Visualizza Media</h1>

    <?php if (!empty($media)): ?>
        <div class="row">
            <?php foreach ($media as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if ($item['tipo_media'] === 'Immagine'): ?>
                            <img src="<?php echo htmlspecialchars($item['url_media']); ?>" class="card-img-top" alt="Media">
                        <?php elseif ($item['tipo_media'] === 'Video'): ?>
                            <video controls class="card-img-top">
                                <source src="<?php echo htmlspecialchars($item['url_media']); ?>" type="video/mp4">
                                Il tuo browser non supporta il video.
                            </video>
                        <?php else: ?>
                            <div class="card-body text-center">
                                <a href="<?php echo htmlspecialchars($item['url_media']); ?>" target="_blank" class="btn btn-primary">Visualizza Documento</a>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['tipo_media']); ?></h5>
                            <p class="card-text"><strong>Descrizione:</strong> <?php echo htmlspecialchars($item['descrizione'] ?? 'Nessuna descrizione'); ?></p>
                            <p class="card-text"><strong>Copyright:</strong> <?php echo htmlspecialchars($item['copyright'] ?? 'Non specificato'); ?></p>
                            <p class="card-text"><strong>Licenza:</strong> <?php echo htmlspecialchars($item['licenza']); ?></p>
                            <p class="card-text"><strong>Data Caricamento:</strong> <?php echo htmlspecialchars($item['data_caricamento']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Non ci sono media caricati.</p>
    <?php endif; ?>

    <div class="mt-4">
        <a href="../index.html" class="btn btn-secondary">Torna alla Home</a>
    </div>
</body>
</html>
