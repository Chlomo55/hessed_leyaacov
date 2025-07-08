<!-- AFFICHE TOUS LES ARTICLES -->

<?php
require_once 'header.php';
// Récupérer tous les articles validés (etat = 2)
$stmt = $pdo->query('SELECT a.*, a.nom AS nom_article, u.prenom, u.nom AS nom_preteur, u.pseudo FROM article a JOIN users u ON a.id_preteur = u.id WHERE a.etat = 2 ORDER BY a.id DESC');
$articles = $stmt->fetchAll();
?>
<h2 style="text-align:center;color:#4e54c8;font-size:2.2em;margin:32px 0 18px 0;letter-spacing:1px;font-weight:800;">Liste des articles disponibles</h2>
<div class="container" style="display:flex;flex-wrap:wrap;gap:36px;justify-content:center;padding-bottom:40px;">
<?php if (empty($articles)): ?>
    <p style="font-size:1.2em;color:#888;text-align:center;width:100%;">Aucun article disponible pour le moment.</p>
<?php else: ?>
    <?php foreach ($articles as $article): ?>
        <div class="card" style="background:#fff;border-radius:18px;box-shadow:0 4px 24px #4e54c820;padding:28px 22px 22px 22px;width:340px;display:flex;flex-direction:column;align-items:center;transition:box-shadow 0.2s;">
            
            <h3 style="color:#4e54c8;font-size:1.35em;font-weight:700;margin-bottom:10px;text-align:center;word-break:break-word;">
                <?= htmlspecialchars($article['nom_article']) ?>
            </h3>
            <?php if (!empty($article['photo_1'])): ?>
                <div style="display:flex;justify-content:center;margin-bottom:16px;">
                    <img src="uploads/<?= htmlspecialchars($article['photo_1']) ?>"
                         alt="Photo de l'article"
                         style="width:260px;height:180px;object-fit:cover;border-radius:14px;box-shadow:0 4px 18px #4e54c830;border:3px solid #4e54c8;transition:transform 0.2s;">
                </div>
            <?php endif; ?>
            <p style="color:#222;font-size:1.08em;margin-bottom:14px;min-height:48px;line-height:1.5;text-align:center;">
                <?= nl2br(htmlspecialchars($article['detail'])) ?>
            </p>
            <p style="color:#666;font-size:1em;margin-bottom:10px;text-align:center;">
                <strong style="color:#4e54c8;">Prêteur :</strong> <?= htmlspecialchars($article['pseudo']) ?>
            </p>
            <a href="detail.php?id=<?= $article['id'] ?>" style="display:inline-block;margin-top:10px;background:#4e54c8;color:#fff;padding:10px 22px;border-radius:8px;text-decoration:none;font-weight:600;font-size:1.08em;box-shadow:0 2px 8px #4e54c820;transition:background 0.2s;">Voir & demander</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>