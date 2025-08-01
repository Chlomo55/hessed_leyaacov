<?php
require_once 'header.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Vérifier que l'utilisateur est admin (à adapter selon ta logique d'authentification)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<p>Accès réservé à l'administrateur.</p>";
    exit;
}

// Traitement de la validation ou du refus
if (isset($_POST['action']) && isset($_POST['id_article'])) {
    $id_article = intval($_POST['id_article']);
    if ($_POST['action'] === 'valider') {
        // Récupérer l'article avant suppression pour obtenir l'email du propriétaire
        $stmt = $pdo->prepare('SELECT a.*, u.mail, u.prenom FROM article a JOIN users u ON a.id_preteur = u.id WHERE a.id = ?');
        $stmt->execute([$id_article]);
        $article = $stmt->fetch();
        $email = $article ? $article['mail'] : null;
        $prenom = $article ? $article['prenom'] : '';
        $nom_article = $article ? $article['nom'] : '';
        // Supprimer l'article
        $stmt = $pdo->prepare('DELETE FROM article WHERE id = ?');
        $stmt->execute([$id_article]);
        $msg = "Article supprimé définitivement.";
        // Envoi du mail si email trouvé
        if ($email) {
            require 'vendor/autoload.php';
            $mail = new PHPMailer(true);
            try {
                $mail->setFrom('no-reply@hessed-leyaacov.fr', 'Hessed LéYaacov');
                $mail->addAddress($email, $prenom);
                $mail->Subject = "Suppression de votre article";
                $mail->isHTML(true);
                $mail->Body = "Bonjour $prenom,<br><br>Votre article '<strong>" . htmlspecialchars($nom_article) . "</strong>' a été définitivement supprimé suite à votre demande d'annulation.<br>L'équipe Hessed LéYaacov.";
                $mail->send();
            } catch (Exception $e) {
                // Optionnel : log ou affichage d'erreur
            }
        }
    } elseif ($_POST['action'] === 'refuser') {
        // Remettre l'article à l'état précédent (par exemple 2 = validé)
        $stmt = $pdo->prepare('UPDATE article SET etat = 2 WHERE id = ?');
        $stmt->execute([$id_article]);
        $msg = "Annulation refusée, l'article est de nouveau visible.";
    }
}

// Récupérer les articles en attente d'annulation
$stmt = $pdo->query('SELECT * FROM article WHERE etat = 4 ORDER BY id DESC');
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Validation des annulations</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 style="text-align:center;color:#e74c3c;margin-top:30px;">Articles en attente d'annulation</h1>
    <?php if (!empty($msg)): ?>
        <p style="color:#2ecc40;font-weight:bold;text-align:center;"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>
    <div class="container" style="max-width:900px;margin:30px auto;">
        <?php if (empty($articles)): ?>
            <p style="font-size:1.2em;color:#888;text-align:center;">Aucun article en attente.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#e9eafc;">
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Propriétaire</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td><?= $article['id'] ?></td>
                        <td><?= htmlspecialchars($article['nom']) ?></td>
                        <td><?= $article['id_preteur'] ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id_article" value="<?= $article['id'] ?>">
                                <button type="submit" name="action" value="valider" style="background:#e74c3c;color:#fff;padding:7px 16px;border:none;border-radius:6px;cursor:pointer;">Valider la suppression</button>
                                <button type="submit" name="action" value="refuser" style="background:#2ecc40;color:#fff;padding:7px 16px;border:none;border-radius:6px;cursor:pointer;margin-left:8px;">Refuser</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
