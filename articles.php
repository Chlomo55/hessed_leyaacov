<!-- AFFICHE TOUS LES ARTICLES -->

<?php
require_once 'header.php';
// Récupérer tous les articles validés (etat = 2)
$stmt = $pdo->query('SELECT a.*, u.prenom, u.nom FROM article a JOIN users u ON a.id_preteur = u.id WHERE a.etat = 2 ORDER BY a.id DESC');
$articles = $stmt->fetchAll();
?>
<h2>Liste des articles disponibles</h2>
<div class="container" style="display:flex;flex-wrap:wrap;gap:24px;justify-content:center;">
<?php if (empty($articles)): ?>
    <p>Aucun article disponible pour le moment.</p>
<?php else: ?>
    <?php foreach ($articles as $article): ?>
        <div class="card" style="background:#fff;border-radius:10px;box-shadow:0 2px 8px #ccc;padding:18px;width:320px;">
            <h3><?= htmlspecialchars($article['nom']) ?></h3>
            <p><?= nl2br(htmlspecialchars($article['detail'])) ?></p>
            <p><strong>Prêteur :</strong> <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?></p>
            <?php if (!empty($article['photo_1'])): ?>
                <img src="uploads/<?= htmlspecialchars($article['photo_1']) ?>" alt="Photo" style="width:220px;height:160px;object-fit:cover;border-radius:8px;" />
            <?php endif; ?>
            <a href="detail.php?id=<?= $article['id'] ?>" style="display:inline-block;margin-top:10px;background:#4e54c8;color:#fff;padding:8px 18px;border-radius:6px;text-decoration:none;">Voir & demander</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>