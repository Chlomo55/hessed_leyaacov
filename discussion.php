<?php
require_once 'header.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['demande'])) {
    echo "<p>Accès refusé.</p>";
    exit;
}
$user_id = $_SESSION['user_id'];
$id_demande = intval($_GET['demande']);
// Vérifier que l'utilisateur est bien concerné par la demande
$stmt = $pdo->prepare('SELECT d.*, a.nom as article_nom, a.id as article_id, u1.prenom as emprunteur_prenom, u1.nom as emprunteur_nom, u2.prenom as preteur_prenom, u2.nom as preteur_nom FROM demande d JOIN article a ON d.id_article = a.id JOIN users u1 ON d.id_emprunteur = u1.id JOIN users u2 ON d.id_preteur = u2.id WHERE d.id = ? AND (d.id_preteur = ? OR d.id_emprunteur = ?)');
$stmt->execute([$id_demande, $user_id, $user_id]);
$demande = $stmt->fetch();
if (!$demande) {
    echo "<p>Demande non trouvée ou accès refusé.</p>";
    exit;
}
// Envoi d'un message texte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['message']) || isset($_FILES['audio']))) {
    $audioFile = null;
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        $dir = 'uploads/audio/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $filename = uniqid('audio_') . '.webm';
        $filepath = $dir . $filename;
        if (move_uploaded_file($_FILES['audio']['tmp_name'], $filepath)) {
            $audioFile = $filename;
        }
    }
    $message = isset($_POST['message']) ? trim($_POST['message']) : null;
    if ($message || $audioFile) {
        $stmt = $pdo->prepare('INSERT INTO messages (id_demande, id_preteur, id_emprunteur, id_expediteur, message, audio, date_envoi) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$id_demande, $demande['id_preteur'], $demande['id_emprunteur'], $user_id, $message, $audioFile]);
    }
    header('Location: discussion.php?demande=' . $id_demande);
    exit;
}
// Récupérer tous les messages de cette discussion (cette demande uniquement)
// Ajout du champ audio si présent
$stmtAll = $pdo->prepare('SELECT m.*, u.prenom, u.nom FROM messages m JOIN users u ON m.id_expediteur = u.id WHERE m.id_demande = ? ORDER BY m.date_envoi ASC');
$stmtAll->execute([$id_demande]);
$allMessages = $stmtAll->fetchAll();
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
.wa-send-btn, .wa-audio-btn {
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
.wa-send-btn:hover, .wa-audio-btn:hover {
    background: #128c7e;
}
.wa-audio-btn.recording {
    background: #e74c3c;
}
.wa-audio-preview {
    margin-left: 10px;
    max-width: 120px;
    vertical-align: middle;
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
            <div class="wa-header-sub">Avec <?= ($user_id == $demande['id_preteur'] ? $demande['emprunteur_prenom'] : $demande['preteur_prenom']) ?></div>
        </div>
        <a href="commencer-pret.php?demande=<?= $id_demande ?>" style="color:#fff;text-decoration:none;font-size:1.1em;background:#128c7e;padding:7px 16px;border-radius:8px;">Commencer le prêt</a>
    </div>
    <div class="wa-messages" id="wa-messages">
        <?php foreach ($allMessages as $msg): ?>
            <div class="wa-bubble <?= $msg['id_expediteur'] == $user_id ? 'me' : 'other' ?>">
                <?php if (!empty($msg['audio'])): ?>
                    <audio controls src="uploads/audio/<?= htmlspecialchars($msg['audio']) ?>" style="max-width:180px;"></audio>
                <?php else: ?>
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                <?php endif; ?>
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
        <button type="button" class="wa-audio-btn" id="wa-audio-btn" title="Enregistrer un audio">🎤</button>
        <audio id="wa-audio-preview" class="wa-audio-preview" controls style="display:none;"></audio>
    </form>
</div>
<script>
// Scroll auto en bas
const waMessages = document.getElementById('wa-messages');
waMessages.scrollTop = waMessages.scrollHeight;
// Audio recording
let recordBtn = document.getElementById('wa-audio-btn');
let audioPreview = document.getElementById('wa-audio-preview');
let waInput = document.getElementById('wa-input');
let mediaRecorder;
let audioChunks = [];
let idDemande = <?php echo json_encode($id_demande); ?>;
let isRecording = false;
recordBtn.addEventListener('click', async function(e) {
    e.preventDefault();
    if (isRecording && mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
        recordBtn.classList.remove('recording');
        recordBtn.textContent = '🎤';
        waInput.disabled = false;
        isRecording = false;
        return;
    }
    if (!navigator.mediaDevices) {
        alert('L\'enregistrement audio n\'est pas supporté sur ce navigateur.');
        return;
    }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        mediaRecorder.ondataavailable = e => {
            if (e.data.size > 0) audioChunks.push(e.data);
        };
        mediaRecorder.onstop = () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            const formData = new FormData();
            formData.append('audio', audioBlob, 'audio.webm');
            formData.append('id_demande', idDemande);
            // Envoi AJAX du message audio
            fetch('discussion.php?demande=' + idDemande, {
                method: 'POST',
                body: formData
            })
            .then(() => location.reload())
            .catch(() => alert('Erreur réseau lors de l\'envoi de l\'audio.'));
        };
        mediaRecorder.start();
        recordBtn.classList.add('recording');
        recordBtn.textContent = '⏹️';
        waInput.disabled = true;
        isRecording = true;
    } catch (err) {
        alert('Erreur lors de l\'accès au micro : ' + err.message);
    }
});
// Empêcher l'envoi vide
const waForm = document.getElementById('wa-form');
waForm.addEventListener('submit', function(e) {
    if (!waInput.value.trim()) {
        e.preventDefault();
    }
});
</script>
</body>
</html>
