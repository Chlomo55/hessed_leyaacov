<!-- AFFICHE TOUS LES ARTICLES -->

<?php
require_once 'header.php';
// Récupérer tous les articles validés (etat = 2)
$stmt = $pdo->query('SELECT a.*, a.nom AS nom_article, u.prenom, u.nom AS nom_preteur, u.pseudo FROM article a JOIN users u ON a.id_preteur = u.id WHERE a.etat = 2 ORDER BY a.id DESC');
$articles = $stmt->fetchAll();
?>
<main class="articles-main" style="background:linear-gradient(120deg,#f7f8fa 0%,#e9eafc 100%);min-height:100vh;padding:0;">
    <h2 style="text-align:center;color:#222;font-size:2.2em;margin:38px 0 24px 0;letter-spacing:1px;font-weight:800;font-family:'Segoe UI','Roboto',Arial,sans-serif;animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);">Liste des articles disponibles</h2>
    <div class="container" style="display:flex;flex-wrap:wrap;gap:38px;justify-content:center;padding-bottom:40px;">
    <?php if (empty($articles)): ?>
        <p style="font-size:1.2em;color:#888;text-align:center;width:100%;">Aucun article disponible pour le moment.</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <div class="card" style="background:rgba(255,255,255,0.98);border-radius:18px;box-shadow:0 8px 32px #4e54c81a;padding:32px 22px 22px 22px;width:340px;display:flex;flex-direction:column;align-items:center;transition:box-shadow 0.2s;animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);">
                <h3 style="color:#222;font-size:1.35em;font-weight:700;margin-bottom:10px;text-align:center;word-break:break-word;font-family:'Segoe UI','Roboto',Arial,sans-serif;"><?= htmlspecialchars($article['nom_article']) ?></h3>
                <?php
                // Récupérer toutes les photos disponibles
                $photos = [];
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($article['photo_' . $i])) {
                        $photos[] = htmlspecialchars($article['photo_' . $i]);
                    }
                }
                ?>
                <?php if (!empty($photos)): ?>
                    <div class="carousel-container" style="position:relative;display:flex;justify-content:center;align-items:center;margin-bottom:16px;width:260px;height:180px;">
                        <button class="carousel-arrow left" style="position:absolute;left:0;top:50%;transform:translateY(-50%);background:rgba(78,84,200,0.9);border:none;color:#fff;font-size:1.6em;border-radius:50%;width:32px;height:32px;cursor:pointer;z-index:2;transition:background 0.2s;">&#8592;</button>
                        <img class="carousel-image" src="uploads/<?= $photos[0] ?>" alt="Photo de l'article" style="width:260px;height:180px;object-fit:cover;border-radius:14px;box-shadow:0 4px 18px #4e54c830;border:2px solid #e9eafc;transition:transform 0.2s;">
                        <button class="carousel-arrow right" style="position:absolute;right:0;top:50%;transform:translateY(-50%);background:rgba(78,84,200,0.9);border:none;color:#fff;font-size:1.6em;border-radius:50%;width:32px;height:32px;cursor:pointer;z-index:2;transition:background 0.2s;">&#8594;</button>
                        <script>
                        // Carrousel JS pour cet article
                        (function() {
                            const container = document.currentScript.parentElement;
                            const images = <?= json_encode($photos) ?>;
                            let idx = 0;
                            const img = container.querySelector('.carousel-image');
                            container.querySelector('.carousel-arrow.left').onclick = function() {
                                idx = (idx - 1 + images.length) % images.length;
                                img.src = 'uploads/' + images[idx];
                            };
                            container.querySelector('.carousel-arrow.right').onclick = function() {
                                idx = (idx + 1) % images.length;
                                img.src = 'uploads/' + images[idx];
                            };
                        })();
                        </script>
                    </div>
                <?php endif; ?>
                <p style="color:#444;font-size:1.08em;margin-bottom:14px;min-height:48px;line-height:1.5;text-align:center;"><?= nl2br(htmlspecialchars($article['detail'])) ?></p>
                <p style="color:#666;font-size:1em;margin-bottom:10px;text-align:center;"><strong style="color:#4e54c8;">Prêteur :</strong> <?= htmlspecialchars($article['pseudo']) ?></p>
                <a href="detail.php?id=<?= $article['id'] ?>" class="article-btn" style="display:inline-block;margin-top:10px;background:linear-gradient(90deg,#e9eafc 0%,#4e54c8 100%);color:#222;padding:10px 22px;border-radius:8px;text-decoration:none;font-weight:600;font-size:1.08em;box-shadow:0 2px 8px #4e54c820;transition:all 0.25s cubic-bezier(.4,2,.6,1);border:1px solid #e9eafc;">Voir & demander</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <div class="back-arrow" onclick="window.history.back()" title="Retour" style="position:fixed;top:20px;left:20px;font-size:2em;color:#4e54c8;cursor:pointer;z-index:100;user-select:none;">&#8592;</div>
</main>
<style>
@keyframes fadeInCard {
    0% { opacity:0; transform:translateY(40px) scale(0.98); }
    100% { opacity:1; transform:translateY(0) scale(1); }
}
.article-btn:hover, .article-btn:focus {
    background:linear-gradient(90deg,#4e54c8 0%,#8f94fb 100%);
    color:#fff;
    box-shadow:0 8px 32px #4e54c830;
    transform:scale(1.04);
    border:1px solid #4e54c8;
}
.card {
    backdrop-filter:blur(2px);
}
.carousel-arrow:hover {
    background:#8f94fb!important;
}
@media (max-width: 700px) {
    .container {
        gap:18px;
        padding:0 0 18px 0;
    }
    .card {
        padding: 18px 6vw;
        font-size: 1em;
        margin-bottom: 10px;
        width:98vw;
        min-width:unset;
    }
    .card h3 {
        font-size: 1.1em;
    }
    .carousel-container {
        width:98vw;
        max-width:340px;
        height:160px;
    }
    .carousel-image {
        width:98vw;
        max-width:340px;
        height:160px;
    }
}
</style>
<?php
require_once 'footer.php';
?>