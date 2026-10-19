<?php
require 'config.php';

$filter = $_GET['categorie'] ?? '';
if ($filter !== '') {
    $sth = $pdo->prepare(
        "SELECT * FROM `event`
         WHERE category = :category
         ORDER BY date ASC"
    );
    $sth->execute([
        'category' => $filter
    ]);
} else {
    $sth = $pdo->prepare(
        "SELECT * FROM `event` WHERE is_validated = 1 AND date >= NOW() ORDER BY date ASC"
    );
    $sth->execute();
}
$events = $sth->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Événements à venir · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<?php require 'header.php'; ?>
<body>
    <section class="hero" style="background-image: url('images/rock.jpg')">
        <div class="hero-content">
            <span class="hero-kicker">Agenda local</span>
            <h1>Les événements près de chez vous</h1>
            <p>Concerts, conférences, ateliers et sport : trouvez votre prochaine sortie ou proposez la vôtre.</p>
            <a class="btn" href="ajouter.php">+ Ajouter un événement</a>
        </div>
    </section>
    <div class="section-head">
        <h2>À venir</h2>
        <!-- La catégorie sélectionnée porte la classe « active » -->
        <ul class="legend">
            <li><a class="active" href="index.php">Toutes</a></li>
            <li class="cat-concert"><a href="index.php?categorie=concert">Concert</a></li>
            <li class="cat-conference"><a href="index.php?categorie=conference">Conférence</a></li>
            <li class="cat-atelier"><a href="index.php?categorie=atelier">Atelier</a></li>
            <li class="cat-sport"><a href="index.php?categorie=sport">Sport</a></li>
        </ul>
    </div>
    <ul class="event-list">
    <?php foreach ($events as $event) { ?>
        <li class="event-card cat-<?= htmlspecialchars($event['category']) ?>">
            <div class="event-media">
                <img class="event-image" src="<?= htmlspecialchars($event['image'] ?? '') ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                <div class="event-date">
                    <span class="day">
                        <?= date('d', strtotime($event['date'])) ?>
                    </span>
                    <span class="month">
                        <?= date('M', strtotime($event['date'])) ?>
                    </span>
                </div>
                <span class="badge">
                    <?= htmlspecialchars($event['category']) ?>
                </span>
            </div>
            <h3 class="event-title">
                <a href="evenement.php?id=<?= (int) $event['id'] ?>">
                    <?= htmlspecialchars($event['title']) ?>
                </a>
            </h3>
            <span class="muted">
                <?= date('d/m/Y', strtotime($event['date'])) ?>
                ·
                <?= date('H\hi', strtotime($event['date'])) ?>
            </span>
        </li>
    <?php } ?>
</ul>
<?php if (empty($events)) { ?>
    <p class="empty">Aucun événement à venir pour le moment.</p>
<?php }
require 'footer.php'; ?>