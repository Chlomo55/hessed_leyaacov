<?php
require_once 'header.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['demande'])) {
    echo "<p>Accès refusé.</p>";
    exit;
}
$user_id = $_SESSION['user_id'];
$id_demande = intval($_GET['demande']);

// Récupérer la demande, l'article et les infos utilisateurs (pseudo, mail, etc)
$stmt = $pdo->prepare('SELECT d.*, a.nom as article_nom, a.caution, a.etat, a.id as article_id, a.detail as article_detail, u1.prenom as emprunteur_prenom, u1.nom as emprunteur_nom, u1.pseudo as emprunteur_pseudo, u1.mail as emprunteur_mail, u2.prenom as preteur_prenom, u2.nom as preteur_nom, u2.pseudo as preteur_pseudo, u2.mail as preteur_mail, d.date_retrait, d.date_retour FROM demande d JOIN article a ON d.id_article = a.id JOIN users u1 ON d.id_emprunteur = u1.id JOIN users u2 ON d.id_preteur = u2.id WHERE d.id = ?');
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();
if (!$demande || $user_id != $demande['id_preteur']) {
    echo "<p>Accès refusé.</p>";
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['certif_pret']) && isset($_POST['certif_caution'])) {
    // Mettre à jour l'état de l'article
    $stmt = $pdo->prepare('UPDATE article SET etat = 3 WHERE id = ?');
    $stmt->execute([$demande['article_id']]);

    // Envoi du mail avec PHPMailer
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // Paramètres SMTP Gmail (à adapter avec tes identifiants)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'chlomo.freoua@gmail.com';
        $mail->Password = 'bpwotttwhkaqmmkl';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('noreply@hessed-leyaacov.fr', 'Hessed Leyaacov');
        $mail->addAddress($demande['preteur_mail']);
        $mail->addAddress($demande['emprunteur_mail']);
        $mail->Subject = 'Le prêt a commencé !';
        $mail->Body = "Bonjour,\n\nLe prêt de l'article '" . $demande['article_nom'] . "' vient de commencer.\n\nRésumé :\n- Article : " . $demande['article_nom'] . "\n- Détail : " . $demande['article_detail'] . "\n- Emprunteur : " . $demande['emprunteur_prenom'] . ' ' . $demande['emprunteur_nom'] . " (pseudo : " . $demande['emprunteur_pseudo'] . ")\n- Prêteur : " . $demande['preteur_prenom'] . ' ' . $demande['preteur_nom'] . " (pseudo : " . $demande['preteur_pseudo'] . ")\n- Montant de la caution : " . $demande['caution'] . " €\n- Date de début : " . date('d/m/Y', strtotime($demande['date_retrait'])) . "\n- Date de retour prévue : " . date('d/m/Y', strtotime($demande['date_retour'])) . "\n\nBon prêt à tous !";
        $mail->send();
        echo '<div style="padding:2em;text-align:center;">Le prêt a bien été lancé et un mail a été envoyé aux deux parties.<br><a href="index.php">Retour à l\'accueil</a></div>';
    } catch (Exception $e) {
        echo '<div style="padding:2em;text-align:center;color:red;">Erreur lors de l\'envoi du mail : ' . $mail->ErrorInfo . '</div>';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commencer le prêt</title>
    <style>
        body { background: #ece5dd; font-family: 'Segoe UI', Arial, sans-serif; }
        .pret-container { max-width: 480px; margin: 2em auto; background: #fff; border-radius: 12px; box-shadow: 0 0 24px rgba(44,62,80,0.10); padding: 2em; }
        .pret-title { font-size: 1.3em; font-weight: bold; margin-bottom: 1em; }
        .pret-info { margin-bottom: 1.2em; }
        .pret-label { font-weight: bold; }
        .pret-checkbox { margin: 1em 0; }
        .pret-btn { background: #128c7e; color: #fff; border: none; border-radius: 8px; padding: 12px 28px; font-size: 1.1em; cursor: pointer; transition: background 0.2s; }
        .pret-btn:disabled { background: #b2dfdb; cursor: not-allowed; }
    </style>
</head>
<body>
<div class="pret-container">
    <div class="pret-title">Démarrer le prêt</div>
    <div class="pret-info"><span class="pret-label">Article :</span> <?= htmlspecialchars($demande['article_nom']) ?></div>
    <div class="pret-info"><span class="pret-label">Détail :</span> <?= nl2br(htmlspecialchars($demande['article_detail'])) ?></div>
    <div class="pret-info"><span class="pret-label">Emprunteur :</span> <?= htmlspecialchars($demande['emprunteur_prenom'] . ' ' . $demande['emprunteur_nom']) ?> (pseudo : <?= htmlspecialchars($demande['emprunteur_pseudo']) ?>)</div>
    <div class="pret-info"><span class="pret-label">Date de début :</span> <?= date('d/m/Y', strtotime($demande['date_retrait'])) ?></div>
    <div class="pret-info"><span class="pret-label">Date de retour prévue :</span> <?= date('d/m/Y', strtotime($demande['date_retour'])) ?></div>
    <div class="pret-info"><span class="pret-label">Montant de la caution :</span> <?= htmlspecialchars($demande['caution']) ?> €</div>
    <form method="post" id="pret-form">
        <div class="pret-checkbox">
            <input type="checkbox" id="certif_pret" name="certif_pret">
            <label for="certif_pret">Je certifie vouloir commencer le prêt immédiatement</label>
        </div>
        <div class="pret-checkbox">
            <input type="checkbox" id="certif_caution" name="certif_caution">
            <label for="certif_caution">Je certifie avoir bien récupéré le chèque de caution de <?= htmlspecialchars($demande['caution']) ?> €</label>
        </div>
        <button type="submit" class="pret-btn" id="pret-btn" disabled>Commencer</button>
    </form>
</div>
<script>
const certifPret = document.getElementById('certif_pret');
const certifCaution = document.getElementById('certif_caution');
const pretBtn = document.getElementById('pret-btn');
function checkForm() {
    pretBtn.disabled = !(certifPret.checked && certifCaution.checked);
}
certifPret.addEventListener('change', checkForm);
certifCaution.addEventListener('change', checkForm);
</script>
</body>
</html>
