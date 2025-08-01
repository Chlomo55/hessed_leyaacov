<?php
require_once 'header.php';
if (!isset($_SESSION['user_id'])) {
    echo "<p>Vous devez être connecté pour annuler un article.</p>";
    exit;
}
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "<p>Article non spécifié.</p>";
    exit;
}
$id_article = intval($_GET['id']);

// Vérifier que l'article appartient à l'utilisateur
$stmt = $pdo->prepare('SELECT * FROM article WHERE id = ? AND id_preteur = ?');
$stmt->execute([$id_article, $user_id]);
$article = $stmt->fetch();
if (!$article) {
    echo "<p>Article introuvable ou accès refusé.</p>";
    exit;
}

// Mettre à jour l'état à 4 (en attente de l'accord de l'admin)
$stmt = $pdo->prepare('UPDATE article SET etat = 4 WHERE id = ?');
$stmt->execute([$id_article]);

// Message de confirmation
echo "<p style='color:#2986cc;font-size:1.2em;'>L'article a été mis en attente d'annulation. L'administrateur doit valider la suppression.</p>";
echo "<a href='articles-preteur.php' style='color:#2ecc40;font-weight:bold;'>Retour à mes articles</a>";
?>