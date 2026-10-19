<?php
require 'config.php';
$errors = [];
$title = '';
$date = '';
$information = '';
$category = 'concert';
$places = '20';
$imagePath = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $information = trim($_POST['information'] ?? '');
    $category = $_POST['category'] ?? 'concert';
    $places = $_POST['places'] ?? '';
    if ($title === '') {
        $errors[] = 'Le titre est obligatoire.';
    }
    if ($date === '') {
        $errors[] = 'La date est obligatoire.';
    } else {
        $dateObject = DateTime::createFromFormat('Y-m-d\TH:i', $date);

        if (!$dateObject || $dateObject->format('Y-m-d\TH:i') !== $date) {
            $errors[] = 'La date est invalide.';
        }
    }
    if ($information === '') {
        $errors[] = 'Les informations sont obligatoires.';
    }
    if (filter_var($places, FILTER_VALIDATE_INT) === false
        || (int) $places <= 0) {
        $errors[] = 'Le nombre de places doit être supérieur à 0.';
    }
    $categories = ['concert', 'conference', 'atelier', 'sport'];
    if (!in_array($category, $categories, true)) {
        $errors[] = 'La catégorie sélectionnée est invalide.';
    }
    if (isset($_FILES['image'])
        && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Une erreur est survenue lors de l'envoi de l'image.";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = "L'image ne doit pas dépasser 5 Mo.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['image']['tmp_name']);
            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];
            if (!isset($allowedTypes[$mime])) {
                $errors[] = 'Format invalide : utilisez JPG, PNG ou WebP.';
            }
        }
    }
    // Enregistrement si toutes les vérifications sont réussies
    if (empty($errors)) {
        try {
            // Envoi de l'image dans le dossier uploads
            if (isset($_FILES['image'])
                && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)
                    && !mkdir($uploadDir, 0755, true)
                    && !is_dir($uploadDir)) {
                    throw new RuntimeException(
                        "Impossible de créer le dossier d'images."
                    );
                }
                $filename = bin2hex(random_bytes(16))
                    . '.' . $allowedTypes[$mime];
                if (!move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $uploadDir . $filename
                )) {
                    throw new RuntimeException(
                        "Impossible d'enregistrer l'image."
                    );
                }
                $imagePath = 'uploads/' . $filename;
            }
            $sql = "INSERT INTO event
                    (title, date, information, category, places, image,
                     is_validated)
                    VALUES
                    (:title, :date, :information, :category, :places, :image,
                     0)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'title'       => $title,
                'date'        => str_replace('T', ' ', $date) . ':00',
                'information' => $information,
                'category'    => $category,
                'places'      => (int) $places,
                'image'       => $imagePath
            ]);
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            // Évite d'afficher les détails techniques aux visiteurs
            error_log($e->getMessage());
            $errors[] = "Une erreur est survenue lors de l'enregistrement.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajouter un événement · Agenda</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require 'header.php'; ?>
<div class="narrow">
    <a class="back-link" href="index.php">
        ← Retour aux événements
    </a>
    <div class="card">
        <h1>Ajouter un événement</h1>
        <p class="muted">
            Votre événement sera visible après validation par un administrateur.
        </p>
        <?php if (!empty($errors)): ?>
            <div class="flash flash-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form class="form" method="post" enctype="multipart/form-data">
            <div>
                <label for="title" class="required">Titre</label>
                <input type="text" id="title" name="title"
                    value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Ex. : Soirée jazz au Kiosque" required>
            </div>
            <div>
                <label for="date" class="required">Date</label>
                <input type="datetime-local" id="date" name="date"
                    value="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>"
                    required>
            </div>
            <div>
                <label for="information" class="required">Informations</label>
                <textarea id="information" name="information"
                    placeholder="Lieu, programme, tarif..." required><?= htmlspecialchars($information, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div>
                <label for="category">Catégorie</label>
                <select id="category" name="category">
                    <option value="concert" <?= $category === 'concert' ? 'selected' : '' ?>>Concert</option>
                    <option value="conference" <?= $category === 'conference' ? 'selected' : '' ?>>Conférence</option>
                    <option value="atelier" <?= $category === 'atelier' ? 'selected' : '' ?>>Atelier</option>
                    <option value="sport" <?= $category === 'sport' ? 'selected' : '' ?>>Sport</option>
                </select>
            </div>
            <div>
                <label for="places">Nombre de places</label>
                <input type="number" id="places" name="places"
                    min="1"
                    value="<?= htmlspecialchars($places, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label for="image">Image à la une</label>
                <input type="file" id="image" name="image"
                    accept="image/jpeg,image/png,image/webp">
                <div class="help-text">JPG, PNG ou WebP (5 Mo maximum).</div>
            </div>
            <button type="submit">Enregistrer l'événement</button>
        </form>
    </div>
</div>
<?php require 'footer.php'; ?>