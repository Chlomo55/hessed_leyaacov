<?php
require_once 'header.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['demande'])) {
    echo "<p>Accès refusé.</p>";
    exit;
}
$user_id = $_SESSION['user_id'];
$id_demande = intval($_GET['demande']);
// Vérifier que l'utilisateur est bien concerné par la demande
$stmt = $pdo->prepare('SELECT d.*, a.nom as article_nom, a.id as article_id, u1.prenom as emprunteur_prenom, u1.nom as emprunteur_nom, u1.id as emprunteur_id, u2.prenom as preteur_prenom, u2.nom as preteur_nom, u2.id as preteur_id FROM demande d JOIN article a ON d.id_article = a.id JOIN users u1 ON d.id_emprunteur = u1.id JOIN users u2 ON d.id_preteur = u2.id WHERE d.id = ?');
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();
if (!$demande || ($user_id != $demande['preteur_id'] && $user_id != $demande['emprunteur_id'])) {
    echo "<p>Demande non trouvée ou accès refusé.</p>";
    exit;
}
// Marquer comme lus tous les messages non lus de cette discussion qui ne sont pas de l'utilisateur
$stmtLu = $pdo->prepare('UPDATE messages SET lu = 1 WHERE id_demande = ? AND id_expediteur != ? AND lu = 0');
$stmtLu->execute([$id_demande, $user_id]);
// Envoi d'un message texte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message) {
        $stmt = $pdo->prepare('INSERT INTO messages (id_demande, id_preteur, id_emprunteur, id_expediteur, message, date_envoi) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$id_demande, $demande['id_preteur'], $demande['id_emprunteur'], $user_id, $message]);
    }
    header('Location: discussion.php?demande=' . $id_demande);
    exit;
}
// Récupérer l'historique complet des échanges pour CETTE demande
$stmtAll = $pdo->prepare('SELECT m.*, u.prenom, u.nom FROM messages m JOIN users u ON m.id_expediteur = u.id WHERE m.id_demande = ? ORDER BY m.date_envoi ASC');
$stmtAll->execute([$id_demande]);
$allMessages = $stmtAll->fetchAll();
// Pour la notification visuelle : récupérer l'ID du dernier message
$lastMsgId = !empty($allMessages) ? end($allMessages)['id'] : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Discussion</title>
<style>
body {
    background: #ece5dd;
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.whatsapp-container {
    max-width: 480px;
    margin: 0 auto;
    background: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 0 24px rgba(44,62,80,0.10);
    position: relative;
}
.wa-header {
    background: #075e54;
    color: #fff;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(7,94,84,0.08);
}
.wa-avatar {
    width: 44px; height: 44px; border-radius: 50%; background: #25d366; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.5em; font-weight: bold; }
.wa-header-info { flex: 1; }
.wa-header-title { font-size: 1.1em; font-weight: bold; }
.wa-header-sub { font-size: 0.97em; color: #b2dfdb; }
.wa-messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px 10px 16px 10px;
    background: #ece5dd;
    display: flex;
    flex-direction: column;
}
.wa-bubble {
    max-width: 75%;
    padding: 10px 16px;
    border-radius: 18px;
    margin-bottom: 10px;
    font-size: 1.05em;
    position: relative;
    word-break: break-word;
    box-shadow: 0 2px 8px rgba(44,62,80,0.04);
}
.wa-bubble.me {
    background: #dcf8c6;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}
.wa-bubble.other {
    background: #fff;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}
.wa-meta {
    font-size: 0.85em;
    color: #888;
    margin-top: 4px;
    text-align: right;
}
.wa-footer {
    background: #f7f7fa;
    padding: 12px 10px 10px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-top: 1px solid #e0e0e0;
}
.wa-input {
    flex: 1;
    border-radius: 22px;
    border: 1px solid #ccc;
    padding: 10px 16px;
    font-size: 1em;
    outline: none;
    background: #fff;
    resize: none;
    min-height: 38px;
    max-height: 90px;
}
.wa-send-btn {
    background: #25d366;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    font-size: 1.4em;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}
.wa-send-btn:hover {
    background: #128c7e;
}
.wa-start-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #fff;
    text-decoration: none;
    font-size: 1.1em;
    background: #128c7e;
    padding: 7px 16px;
    border-radius: 8px;
    position: absolute;
    right: 18px;
    top: 16px;
    transition: background 0.2s;
}
.wa-start-btn:hover {
    background: #075e54;
}
@media (max-width: 600px) {
    .whatsapp-container { max-width: 100vw; min-height: 100vh; }
    .wa-header { padding: 12px 6px; }
    .wa-footer { padding: 8px 4px; }
}
</style>
<body>
<div class="whatsapp-container">
    <div class="wa-header">
        <div class="wa-avatar"><?= strtoupper(substr($demande['article_nom'],0,1)) ?></div>
        <div class="wa-header-info">
            <div class="wa-header-title">Article : <?= htmlspecialchars($demande['article_nom']) ?></div>
            <div class="wa-header-sub">Avec <?= htmlspecialchars($demande['emprunteur_prenom']) ?></div>
        </div>
        <a href="commencer-pret.php?demande=<?= $id_demande ?>" title="Commencer le prêt" class="wa-start-btn">
            <span style="font-size:1.3em;vertical-align:middle;">&#9654;</span> <span style="font-size:1em;vertical-align:middle;">Commencer</span>
        </a>
    </div>
    <div class="wa-messages" id="wa-messages">
        <?php
        // Afficher le message initial de demande de l'emprunteur
        if (!empty($demande['message'])): ?>
            <div class="wa-bubble other" style="background:#fff;border:2px solid #4e54c8;">
                <?= nl2br(htmlspecialchars($demande['message'])) ?>
                <div class="wa-meta">
                    <?= htmlspecialchars($demande['emprunteur_prenom'] . ' ' . strtoupper(substr($demande['emprunteur_nom'],0,1))) ?>
                    [Demande initiale]
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ($allMessages as $msg): ?>
            <div class="wa-bubble <?= $msg['id_expediteur'] == $user_id ? 'me' : 'other' ?>">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                <div class="wa-meta">
                    <?= htmlspecialchars($msg['prenom'] . ' ' . strtoupper(substr($msg['nom'],0,1))) ?>
                    [<?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?>]
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="post" class="wa-footer" enctype="multipart/form-data" id="wa-form">
        <textarea name="message" class="wa-input" id="wa-input" placeholder="Écrire un message..."></textarea>
        <button type="submit" class="wa-send-btn" title="Envoyer"><span>➤</span></button>
    </form>
</div>
<script>
const waMessages = document.getElementById('wa-messages');
let idUser = <?php echo json_encode($user_id); ?>;
let idDemande = <?php echo json_encode($id_demande); ?>;
let lastMsgId = 0;
let lastMessagesContent = '';
const demandeInitiale = <?php echo json_encode(!empty($demande['message']) ? [
    'message' => $demande['message'],
    'prenom' => $demande['emprunteur_prenom'],
    'nom' => $demande['emprunteur_nom'],
    'type' => 'initiale'
] : null); ?>;
function renderMessage(msg) {
    if (msg.type === 'initiale') {
        return `<div class='wa-bubble other' style='background:#fff;border:2px solid #4e54c8;'>${msg.message.replace(/\n/g,'<br>')}<div class='wa-meta'>${msg.prenom} ${msg.nom.charAt(0).toUpperCase()} [Demande initiale]</div></div>`;
    }
    return `<div class='wa-bubble ${msg.id_expediteur == idUser ? 'me' : 'other'}'>${msg.message ? msg.message.replace(/\n/g,'<br>') : ''}<div class='wa-meta'>${msg.prenom} ${msg.nom.charAt(0).toUpperCase()} [${new Date(msg.date_envoi).toLocaleString('fr-FR',{day:'2-digit',month:'2-digit',year:'2-digit',hour:'2-digit',minute:'2-digit'})}]</div></div>`;
}
function fetchAllMessages() {
    if (!waMessages) {
        console.error('Conteneur wa-messages introuvable');
        return;
    }
    fetch('get_messages.php?demande='+idDemande+'&last_id=0')
        .then(r => r.json())
        .then(function(data) {
            let html = '';
            if (demandeInitiale) {
                html += renderMessage(demandeInitiale);
            }
            if (data.messages) {
                data.messages.forEach(msg => {
                    html += renderMessage(msg);
                    lastMsgId = msg.id;
                });
            }
            if (html !== lastMessagesContent) {
                waMessages.innerHTML = html;
                waMessages.scrollTop = waMessages.scrollHeight;
                lastMessagesContent = html;
            }
        })
        .catch(function(e) { console.error('Erreur AJAX', e); });
}
setInterval(fetchAllMessages, 2000);
fetchAllMessages(); // premier affichage immédiat
// ENVOI INSTANTANÉ DU MESSAGE
const waForm = document.getElementById('wa-form');
const waInput = document.getElementById('wa-input');
waForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const message = waInput.value.trim();
    if (!message) return;
    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'demande='+encodeURIComponent(idDemande)+'&message='+encodeURIComponent(message)
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) {
            waInput.value = '';
            fetchAllMessages(); // Rafraîchit la liste des messages juste après l'envoi
        }
    })
    .catch(function(e) { console.error('Erreur envoi', e); });
});
</script>
</body>
</html>
