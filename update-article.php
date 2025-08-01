<?php
require_once 'header.php';
if (!isset($_SESSION['user_id'])) {
    echo "<p>Vous devez être connecté pour modifier un article.</p>";
    exit;
}
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "<p>Article non spécifié.</p>";
    exit;
}
$id_article = intval($_GET['id']);

// Récupérer l'article
$stmt = $pdo->prepare('SELECT * FROM article WHERE id = ? AND id_preteur = ?');
$stmt->execute([$id_article, $user_id]);
$article = $stmt->fetch();
if (!$article) {
    echo "<p>Article introuvable ou accès refusé.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $detail = trim($_POST['detail']);
    $pref = trim($_POST['pref']);
    // Ajoute ici les autres champs à modifier si besoin
    $stmt = $pdo->prepare('UPDATE article SET nom = ?, detail = ?, pref = ? WHERE id = ?');
    $stmt->execute([$nom, $detail, $pref, $id_article]);
    echo "<p style='color:#2ecc40;font-size:1.2em;'>Article modifié avec succès.</p>";
    echo "<a href='articles-preteur.php' style='color:#2986cc;font-weight:bold;'>Retour à mes articles</a>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'article</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 style="text-align:center;color:#2986cc;margin-top:30px;">Modifier l'article</h1>
    <form method="post" style="max-width:500px;margin:30px auto;background:#fff;padding:28px;border-radius:12px;box-shadow:0 4px 18px #4e54c830;">
        <label>Nom :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($article['nom']) ?>" required style="width:100%;padding:8px;margin-bottom:14px;"><br>
        <label>Détail :</label><br>
        <textarea name="detail" required style="width:100%;height:80px;padding:8px;margin-bottom:14px;"><?= htmlspecialchars($article['detail']) ?></textarea><br>
        <label>Préférences de contact :</label><br>
        <input type="text" name="pref" value="<?= htmlspecialchars($article['pref']) ?>" style="width:100%;padding:8px;margin-bottom:14px;"><br>
        <!-- Ajoute ici d'autres champs si besoin -->
        <button type="submit" style="background:#2986cc;color:#fff;padding:10px 22px;border:none;border-radius:6px;cursor:pointer;font-size:1.1em;">Enregistrer</button>
        <a href="articles-preteur.php" style="margin-left:18px;color:#e74c3c;font-weight:bold;">Annuler</a>
    </form>
</body>
</html>
