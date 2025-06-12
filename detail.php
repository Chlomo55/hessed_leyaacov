<?php
require_once 'header.php';
if (!isset($_GET['id'])) {
    echo "<p>Article non spécifié.</p>";
    exit;
}
$article_id = intval($_GET['id']);
$stmt = $pdo->prepare('SELECT a.*, u.nom as preteur_nom, u.prenom as preteur_prenom FROM article a JOIN users u ON a.id_preteur = u.id WHERE a.id = ?');
$stmt->execute([$article_id]);
$article = $stmt->fetch();
if (!$article) {
    echo "<p>Article introuvable.</p>";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $id_emprunteur = $_SESSION['user_id'];
    $date_retrait = $_POST['date_retrait'] ?? '';
    $date_retour = $_POST['date_retour'] ?? '';
    $message = trim($_POST['message'] ?? '');
    if ($date_retrait && $date_retour && $message) {
        $stmt = $pdo->prepare('INSERT INTO demande (id_article, id_preteur, id_emprunteur, date_retrait, date_retour, message, statut) VALUES (?, ?, ?, ?, ?, ?, 0)');
        $stmt->execute([$article_id, $article['id_preteur'], $id_emprunteur, $date_retrait, $date_retour, $message]);
        echo "<p style=\"color:green\">Votre demande a été envoyée !</p>";
    } else {
        echo "<p style=\"color:red\">Veuillez remplir tous les champs.</p>";
    }
}
?>
<h2>Détail de l'article</h2>
<div>
    <h3><?= htmlspecialchars($article['nom']) ?></h3>
    <p><?= nl2br(htmlspecialchars($article['detail'])) ?></p>
    <p><strong>Prêteur :</strong> <?= htmlspecialchars($article['preteur_prenom'] . ' ' . $article['preteur_nom']) ?></p>
</div>
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $article['id_preteur']): ?>
    <h3>Demander cet article</h3>
    <form method="post">
        <label>Date de retrait : <input type="date" name="date_retrait" required></label><br><br>
        <label>Date de retour : <input type="date" name="date_retour" required></label><br><br>
        <label>Message :<br><textarea name="message" required></textarea></label><br><br>
        <button type="submit">Envoyer la demande</button>
    </form>
<?php elseif (!isset($_SESSION['user_id'])): ?>
    <p><a href="connexion.php">Connectez-vous</a> pour faire une demande.</p>
<?php endif; ?>
<a href="articles.php">&larr; Retour aux articles</a>