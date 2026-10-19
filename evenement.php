<?php
require 'config.php';

// Récupérer l'ID dans l'URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("ID d'événement invalide.");
}

// Récupérer l'événement correspondant
$sth = $pdo->prepare(
    'SELECT * FROM `event` WHERE id = :id'
);

$sth->execute([
    'id' => $id
]);

$event = $sth->fetch(PDO::FETCH_ASSOC);

// Vérifier que l'événement existe
if (!$event) {
    http_response_code(404);
    header('Location: 404.html');
exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soirée jazz au Kiosque · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<?php require 'header.php'; ?>
<body>

<a class="back-link" href="index.php">← Retour aux événements</a>
<article class="cat-concert">
    <header class="detail-hero"
        style="background-image: url(<?= htmlspecialchars($event['image'] ?? '')  ?>)">
        <div class="detail-hero-content">
            <span class="badge">
                <?= htmlspecialchars($event['category']) ?>
            </span>
            <h1>
                <?= htmlspecialchars($event['title']) ?>
            </h1>
            <p>
                <?= date('d/m/Y', strtotime($event['date'])) ?>
            </p>
        </div>
    </header>
    <div class="detail-grid">
        <section class="card">
            <h2>Informations</h2>

            <p>
                <?= nl2br(htmlspecialchars($event['information'])) ?>
            </p>
        </section>

        <aside class="card booking">
            <p class="places">
                <strong>
                    <?= (int) $event['places'] ?>
                </strong>
                places restantes
            </p>

            <form method="post">
                <button type="submit" class="btn btn-block">
                    S'inscrire
                </button>
            </form>
        </aside>
    </div>
</article>
<?php require 'footer.php'; ?>