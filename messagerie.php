<?php
require_once 'header.php';
if (!isset($_SESSION['user_id'])) {
    echo "<p>Vous devez être connecté pour accéder à la messagerie.</p>";
    exit;
}
$user_id = $_SESSION['user_id'];
// Récupérer les demandes où l'utilisateur est prêteur ou emprunteur
$stmt = $pdo->prepare('SELECT d.*, a.nom as article_nom, u1.prenom as emprunteur_prenom, u1.nom as emprunteur_nom, u2.prenom as preteur_prenom, u2.nom as preteur_nom FROM demande d JOIN article a ON d.id_article = a.id JOIN users u1 ON d.id_emprunteur = u1.id JOIN users u2 ON d.id_preteur = u2.id WHERE d.id_preteur = ? OR d.id_emprunteur = ? ORDER BY d.id DESC');
$stmt->execute([$user_id, $user_id]);
$demandes = $stmt->fetchAll();
?>
<h2>Messagerie</h2>
<style>
.liste-discussions { max-width: 600px; margin: 30px auto; }
.discussion-item { background: #f7f7f7; border-radius: 10px; padding: 16px; margin-bottom: 12px; box-shadow: 0 2px 8px #eee; display: flex; align-items: center; cursor: pointer; transition: background 0.2s; text-decoration: none; color: #222; }
.discussion-item:hover { background: #e6eaff; }
.disc-avatar { width: 44px; height: 44px; border-radius: 50%; background: #4e54c8; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3em; margin-right: 18px; }
.disc-info { flex: 1; }
.disc-title { font-weight: bold; color: #4e54c8; }
.disc-msg { color: #555; font-size: 0.98em; margin-top: 2px; }
</style>
<div class="liste-discussions">
<?php if (empty($demandes)): ?>
    <p>Aucune demande trouvée.</p>
<?php else: ?>
    <?php foreach ($demandes as $demande): ?>
        <a class="discussion-item" href="discussion.php?demande=<?= $demande['id'] ?>">
            <div class="disc-avatar">
                <?= strtoupper(substr($demande['article_nom'],0,1)) ?>
            </div>
            <div class="disc-info">
                <div class="disc-title">
                    <?= htmlspecialchars($demande['article_nom']) ?>
                </div>
                <div class="disc-msg">
                    <?php
                    $msg = $demande['message'];
                    echo strlen($msg) > 60 ? htmlspecialchars(substr($msg,0,60)).'...' : htmlspecialchars($msg);
                    ?>
                </div>
                <div style="font-size:0.93em;color:#888;">
                    Du <?= htmlspecialchars($demande['date_retrait']) ?> au <?= htmlspecialchars($demande['date_retour']) ?>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>
</div>
