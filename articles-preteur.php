<!-- AFFICHE TOUS LES ARTICLES DU PRETEUR -->

<?php
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p>Vous devez être connecté pour voir vos articles.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Compter les demandes en attente pour les articles du prêteur
$stmtNotif = $pdo->prepare('SELECT COUNT(*) FROM demande WHERE id_preteur = ? AND statut = 0');
$stmtNotif->execute([$user_id]);
$nbDemandes = $stmtNotif->fetchColumn();

$stmt = $pdo->prepare('SELECT * FROM article WHERE id_preteur = ? ORDER BY id DESC');
$stmt->execute([$user_id]);
$articles = $stmt->fetchAll();

function getEtatLabel($etat) {
    switch ($etat) {
        case 0:
            return ['Non autorisé', '#e74c3c']; // Rouge
        case 1:
            return ['En attente', '#ff9800']; // Orange
        case 2:
            return ['Validé', '#2ecc40']; // Vert
        case 3:
            return ['En cours de prêt', '#2986cc']; // Bleu
        case 4:
            return ['En attente d\'annulation', '#f1c40f']; // Jaune
        default:
            return ['Inconnu', '#888'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes articles à prêter</title>
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            width: 320px;
            padding: 24px 20px 18px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            transition: box-shadow 0.2s;
        }
        .card:hover {
            box-shadow: 0 8px 32px rgba(78,84,200,0.18);
        }
        .card img {
            width: 100%;
            max-width: 220px;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 18px;
            background: #eee;
        }
        .card-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #2d3a4b;
            margin-bottom: 8px;
            text-align: center;
        }
        .card-detail {
            color: #555;
            font-size: 1em;
            margin-bottom: 14px;
            text-align: center;
        }
        .etat {
            font-weight: bold;
            padding: 7px 18px;
            border-radius: 20px;
            font-size: 1em;
            margin-bottom: 10px;
            display: inline-block;
        }
        .notif-badge {
            display: inline-block;
            background: #e74c3c;
            color: #fff;
            border-radius: 50%;
            padding: 4px 10px;
            font-size: 1em;
            margin-left: 6px;
            vertical-align: middle;
        }
        .notif-link {
            text-decoration: none;
            color: #4e54c8;
            font-weight: bold;
            margin-bottom: 18px;
            display: inline-block;
        }
        @media (max-width: 700px) {
            .container { flex-direction: column; gap: 18px; align-items: center; }
            .card { width: 95vw; max-width: 350px; }
        }
    </style>
</head>
<body>
    <h1 style="text-align:center;margin-top:30px;color:#4e54c8;">Mes articles à prêter</h1>
    <div style="text-align:center;margin-bottom:18px;">
        <a href="messagerie.php" class="notif-link">
            Messagerie
            <?php if ($nbDemandes > 0): ?>
                <span class="notif-badge"><?php echo $nbDemandes; ?></span>
            <?php endif; ?>
        </a>
    </div>
    <div class="container">
        <?php if (empty($articles)): ?>
            <p style="font-size:1.2em;color:#888;">Aucun article trouvé.</p>
        <?php else: ?>
            <?php foreach ($articles as $index => $article): 
                $etat = getEtatLabel($article['etat']);
                $photos = [];
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($article['photo_' . $i])) {
                        $photos[] = 'uploads/' . htmlspecialchars($article['photo_' . $i]);
                    }
                }
                if (empty($photos)) {
                    $photos[] = 'https://via.placeholder.com/220x180?text=Pas+de+photo';
                }
            ?>
            <div class="card flip-card" id="card-<?php echo $index; ?>">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <img src="<?php echo $photos[0]; ?>" alt="Photo article">
                        <div class="card-title"><?php echo htmlspecialchars($article['nom']); ?></div>
                        <div class="card-detail"><?php echo nl2br(htmlspecialchars($article['detail'])); ?></div>
                        <span class="etat" style="background:<?php echo $etat[1]; ?>;color:#fff;">
                            <?php if ($article['etat'] == 3): ?>
                                <a href="suivi_pret.php?id=<?php echo $article['id']; ?>" style="color:#fff;text-decoration:underline;">
                                    <?php echo $etat[0]; ?>
                                </a>
                            <?php else: ?>
                                <?php echo $etat[0]; ?>
                            <?php endif; ?>
                        </span>
                        <button class="voir-infos-btn" data-index="<?php echo $index; ?>">Voir les infos</button>
                        <form method="get" action="delete-article.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                            <button type="submit" style="background:#e74c3c;color:#fff;padding:7px 16px;border:none;border-radius:6px;cursor:pointer;margin-left:8px;">Annuler</button>
                        </form>
                        <form method="get" action="update-article.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                            <button type="submit" style="background:#2986cc;color:#fff;padding:7px 16px;border:none;border-radius:6px;cursor:pointer;margin-left:8px;">Modifier</button>
                        </form>
                    </div>
                    <div class="flip-card-back">
                        <div class="carousel" id="carousel-<?php echo $index; ?>">
                            <?php foreach ($photos as $k => $photo): ?>
                                <img src="<?php echo $photo; ?>" class="carousel-img" style="display:<?php echo $k === 0 ? 'block' : 'none'; ?>;width:220px;height:180px;object-fit:cover;border-radius:10px;background:#eee;" />
                            <?php endforeach; ?>
                            <?php if (count($photos) > 1): ?>
                                <button class="carousel-prev" data-index="<?php echo $index; ?>">&#8592;</button>
                                <button class="carousel-next" data-index="<?php echo $index; ?>">&#8594;</button>
                            <?php endif; ?>
                        </div>
                        <div class="infos-back">
                            <div><strong>Nom :</strong> <?php echo htmlspecialchars($article['nom']); ?></div>
                            <div><strong>Détail :</strong> <?php echo nl2br(htmlspecialchars($article['detail'])); ?></div>
                            <div><strong>Préférences de contact :</strong> <?php echo htmlspecialchars($article['pref']); ?></div>
                            <div><strong>État :</strong> <span style="color:<?php echo $etat[1]; ?>;font-weight:bold;"> <?php echo $etat[0]; ?></span></div>
                        </div>
                        <button class="retour-btn" data-index="<?php echo $index; ?>" style="margin-top:10px;">Retour</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script>
    // Flip card JS
    document.querySelectorAll('.voir-infos-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const idx = this.getAttribute('data-index');
            document.getElementById('card-' + idx).classList.add('flipped');
        });
    });
    document.querySelectorAll('.retour-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const idx = this.getAttribute('data-index');
            document.getElementById('card-' + idx).classList.remove('flipped');
        });
    });
    // Carousel JS
    document.querySelectorAll('.carousel').forEach(function(carousel, idx) {
        const imgs = carousel.querySelectorAll('.carousel-img');
        let current = 0;
        const showImg = (i) => {
            imgs.forEach((img, k) => img.style.display = (k === i ? 'block' : 'none'));
        };
        const prevBtn = carousel.querySelector('.carousel-prev');
        const nextBtn = carousel.querySelector('.carousel-next');
        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', function() {
                current = (current - 1 + imgs.length) % imgs.length;
                showImg(current);
            });
            nextBtn.addEventListener('click', function() {
                current = (current + 1) % imgs.length;
                showImg(current);
            });
        }
    });
    </script>
    <style>
    .flip-card { perspective: 1000px; }
    .flip-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.7s cubic-bezier(.4,2,.6,1);
        transform-style: preserve-3d;
        min-height: 340px;
    }
    .flipped .flip-card-inner { transform: rotateY(180deg); }
    .flip-card-front, .flip-card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 10px 0 0 0;
    }
    .flip-card-back {
        background: #f4f7fa;
        border-radius: 14px;
        transform: rotateY(180deg);
        z-index: 2;
    }
    .flip-card-front {
        background: #fff;
        z-index: 1;
    }
    .voir-infos-btn, .retour-btn, .carousel-prev, .carousel-next {
        background: #4e54c8;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 7px 16px;
        margin-top: 10px;
        font-size: 1em;
        cursor: pointer;
        transition: background 0.2s;
    }
    .voir-infos-btn:hover, .retour-btn:hover, .carousel-prev:hover, .carousel-next:hover {
        background: #8f94fb;
    }
    .carousel {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    .infos-back {
        width: 100%;
        text-align: left;
        margin-top: 10px;
        color: #333;
        font-size: 1em;
    }
    </style>
</body>
</html>
