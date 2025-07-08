<?php
require_once 'header.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['demande'])) {
    echo "<p>Accès refusé.</p>";
    exit;
}
$user_id = $_SESSION['user_id'];
$id_demande = intval($_GET['demande']);

// Récupérer les infos du prêt
$stmt = $pdo->prepare('SELECT d.*, a.nom as article_nom, u1.prenom as emprunteur_prenom, u1.nom as emprunteur_nom, u2.prenom as preteur_prenom, u2.nom as preteur_nom FROM demande d JOIN article a ON d.id_article = a.id JOIN users u1 ON d.id_emprunteur = u1.id JOIN users u2 ON d.id_preteur = u2.id WHERE d.id = ?');
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();
if (!$demande) {
    echo "<p>Prêt non trouvé.</p>";
    exit;
}
$date_debut = strtotime($demande['date_retrait']);
$date_fin = strtotime($demande['date_retour']);
$date_now = time();
$progress = 0;
if ($date_now > $date_fin) {
    $progress = 100;
} elseif ($date_now > $date_debut) {
    $progress = round(100 * ($date_now - $date_debut) / max(1, $date_fin - $date_debut));
} else {
    $progress = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi du prêt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(120deg, #8f94fb 0%, #4e54c8 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .suivi-container {
            max-width: 600px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(78,84,200,0.18);
            padding: 38px 32px 38px 32px;
            position: relative;
        }
        .suivi-title {
            font-size: 1.5em;
            font-weight: bold;
            color: #4e54c8;
            margin-bottom: 18px;
            text-align: center;
        }
        .suivi-info {
            text-align: center;
            margin-bottom: 30px;
            color: #2d3a4b;
            font-size: 1.13em;
        }
        .timeline {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 40px 0 30px 0;
            position: relative;
        }
        .timeline-bar {
            position: absolute;
            top: 50%;
            left: 60px;
            right: 60px;
            height: 8px;
            background: #e0e4fa;
            border-radius: 6px;
            z-index: 1;
        }
        .timeline-progress {
            position: absolute;
            top: 50%;
            left: 60px;
            height: 8px;
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            border-radius: 6px;
            z-index: 2;
            transition: width 0.7s cubic-bezier(.4,2,.6,1);
        }
        .timeline-step {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 120px;
        }
        .timeline-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid #4e54c8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3em;
            font-weight: bold;
            color: #4e54c8;
            box-shadow: 0 2px 10px rgba(78,84,200,0.10);
        }
        .timeline-step.current .timeline-dot {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            border-color: #8f94fb;
        }
        .timeline-label {
            margin-top: 10px;
            font-size: 1em;
            color: #4e54c8;
            font-weight: 600;
            text-align: center;
        }
        .timeline-date {
            margin-top: 4px;
            font-size: 0.97em;
            color: #888;
            text-align: center;
        }
        @media (max-width: 700px) {
            .suivi-container { padding: 16px 2vw; }
            .timeline-step { width: 80px; }
            .timeline-bar, .timeline-progress { left: 30px; right: 30px; }
        }
    </style>
</head>
<body>
<div class="suivi-container">
    <div class="suivi-title">Suivi du prêt de "<?= htmlspecialchars($demande['article_nom']) ?>"</div>
    <div class="suivi-info">
        Prêteur : <?= htmlspecialchars($demande['preteur_prenom'] . ' ' . $demande['preteur_nom']) ?><br>
        Emprunteur : <?= htmlspecialchars($demande['emprunteur_prenom'] . ' ' . $demande['emprunteur_nom']) ?>
    </div>
    <div class="timeline">
        <div class="timeline-bar"></div>
        <div class="timeline-progress" style="width:<?= $progress ?>%;"></div>
        <div class="timeline-step<?= ($progress == 0 ? ' current' : '') ?>">
            <div class="timeline-dot">🚚</div>
            <div class="timeline-label">Début</div>
            <div class="timeline-date"><?= date('d/m/Y', $date_debut) ?></div>
        </div>
        <div class="timeline-step current">
            <div class="timeline-dot">📍</div>
            <div class="timeline-label">Aujourd'hui</div>
            <div class="timeline-date"><?= date('d/m/Y', $date_now) ?></div>
        </div>
        <div class="timeline-step<?= ($progress == 100 ? ' current' : '') ?>">
            <div class="timeline-dot">🏁</div>
            <div class="timeline-label">Fin</div>
            <div class="timeline-date"><?= date('d/m/Y', $date_fin) ?></div>
        </div>
    </div>
    <div style="text-align:center;color:#4e54c8;font-size:1.1em;margin-top:30px;">
        <?= ($progress < 100) ? 'Le prêt est en cours.' : 'Le prêt est terminé.' ?>
    </div>
</div>
</body>
</html>
