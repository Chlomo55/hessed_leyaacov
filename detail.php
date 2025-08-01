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
    $heure_retrait = $_POST['heure_retrait'] ?? '';
    $date_retour = $_POST['date_retour'] ?? '';
    $heure_retour = $_POST['heure_retour'] ?? '';
    $message = trim($_POST['message'] ?? '');
    if ($date_retrait && $heure_retrait && $date_retour && $heure_retour && $message) {
        $stmt = $pdo->prepare('INSERT INTO demande (id_article, id_preteur, id_emprunteur, date_retrait, heure_retrait, date_retour, heure_retour, message, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)');
        $stmt->execute([$article_id, $article['id_preteur'], $id_emprunteur, $date_retrait, $heure_retrait, $date_retour, $heure_retour, $message]);
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
    <form method="post" id="demande-form" style="max-width:420px;background:#f7f7fa;padding:24px 28px;border-radius:18px;box-shadow:0 2px 12px #eee;margin-bottom:30px;">
        <div style="margin-bottom:18px;">
            <label>Date de retrait : <input type="date" name="date_retrait" required style="margin-left:8px;margin-right:18px;">
                Heure : <select name="heure_retrait" id="heure_retrait" required style="margin-left:8px;"></select>
                <span style="color:#888;font-size:0.95em;margin-left:8px;">(entre <?= htmlspecialchars(substr($article['heure_retrait_debut'],0,5)) ?> et <?= htmlspecialchars(substr($article['heure_retrait_fin'],0,5)) ?>)</span>
            </label>
        </div>
        <div style="margin-bottom:18px;">
            <label>Date de retour : <input type="date" name="date_retour" required style="margin-left:8px;margin-right:18px;">
                Heure : <select name="heure_retour" id="heure_retour" required style="margin-left:8px;"></select>
                <span style="color:#888;font-size:0.95em;margin-left:8px;">(entre <?= htmlspecialchars(substr($article['heure_retour_debut'],0,5)) ?> et <?= htmlspecialchars(substr($article['heure_retour_fin'],0,5)) ?>)</span>
            </label>
        </div>
        <div style="margin-bottom:18px;">
            <label>Message :<br><textarea name="message" required style="width:100%;min-height:80px;border-radius:10px;padding:10px;"></textarea></label>
        </div>
        <button type="submit" style="background:linear-gradient(90deg,#4e54c8 0%,#8f94fb 100%);color:#fff;border:none;border-radius:10px;padding:12px 0;font-size:1.13em;font-weight:bold;box-shadow:0 2px 10px #eee;cursor:pointer;">Envoyer la demande</button>
    </form>
    <script>
    // Remplir les heures possibles dans les select
    function fillHourSelect(selectId, start, end) {
        const select = document.getElementById(selectId);
        select.innerHTML = '';
        let startHour = parseInt(start.split(':')[0]);
        let startMin = parseInt(start.split(':')[1]);
        let endHour = parseInt(end.split(':')[0]);
        let endMin = parseInt(end.split(':')[1]);
        for (let h = startHour; h <= endHour; h++) {
            let minStart = (h === startHour) ? startMin : 0;
            let minEnd = (h === endHour) ? endMin : 59;
            for (let m = minStart; m <= minEnd; m += 15) {
                let hh = h.toString().padStart(2, '0');
                let mm = m.toString().padStart(2, '0');
                let val = `${hh}:${mm}`;
                let option = document.createElement('option');
                option.value = val;
                option.text = val;
                select.appendChild(option);
            }
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        fillHourSelect('heure_retrait', '<?= htmlspecialchars($article['heure_retrait_debut']) ?>', '<?= htmlspecialchars($article['heure_retrait_fin']) ?>');
        fillHourSelect('heure_retour', '<?= htmlspecialchars($article['heure_retour_debut']) ?>', '<?= htmlspecialchars($article['heure_retour_fin']) ?>');
    });
    </script>
<?php elseif (!isset($_SESSION['user_id'])): ?>
    <p><a href="connexion.php">Connectez-vous</a> pour faire une demande.</p>
<?php endif; ?>
<a href="articles.php">&larr; Retour aux articles</a>