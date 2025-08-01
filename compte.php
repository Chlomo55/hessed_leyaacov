<!-- COMPTE DE L'USER -->

<?php
require_once 'header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT nom, prenom, pseudo, num, mail, adresse, ville, type_logement, etage, interphone FROM users WHERE id = :id');
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Notification messages non lus pour l'emprunteur
$stmtNotif = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE id_emprunteur = ? AND id_expediteur != ? AND lu = 0');
$stmtNotif->execute([$user_id, $user_id]);
$nbNotif = $stmtNotif->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon compte</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .compte-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 40px;
        }
        .compte-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .compte-header h2 {
            margin: 0;
            color: #2d3a4b;
        }
        .user-infos {
            margin-bottom: 32px;
        }
        .user-infos p {
            margin: 8px 0;
            color: #444;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .action-btn {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 16px 28px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 2px 8px rgba(78,84,200,0.08);
            text-decoration: none;
            text-align: center;
        }
        .action-btn:hover {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
            transform: translateY(-2px) scale(1.03);
        }
        @media (max-width: 600px) {
            .compte-container {
                padding: 20px 8px;
            }
            .actions {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
<main class="compte-main" style="background:linear-gradient(120deg,#f7f8fa 0%,#e9eafc 100%);min-height:100vh;padding:0;">
    <div class="compte-container" style="max-width:600px;margin:60px auto 0 auto;background:rgba(255,255,255,0.98);border-radius:18px;box-shadow:0 8px 32px #4e54c81a;padding:44px 38px;animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);">
        <div class="compte-header" style="text-align:center;margin-bottom:32px;">
            <h2 style="margin:0;color:#222;font-size:2em;font-weight:700;font-family:'Segoe UI','Roboto',Arial,sans-serif;letter-spacing:1px;">Bienvenue, <?php echo htmlspecialchars($user['prenom']); ?> !</h2>
            <p style="color:#888;font-size:1.08em;">Votre espace personnel</p>
        </div>
        <div class="user-infos" id="user-infos" style="display:none;position:relative;animation:fadeInCard 1.2s cubic-bezier(.4,2,.6,1);background:#f7f8fa;border-radius:14px;padding:18px 22px;margin-bottom:18px;box-shadow:0 2px 12px #e9eafc;">
            <span id="close-infos" style="position:absolute;top:10px;right:10px;cursor:pointer;font-size:22px;color:#e74c3c;font-weight:bold;">&times;</span>
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['nom']); ?></p>
            <p><strong>Prénom :</strong> <?php echo htmlspecialchars($user['prenom']); ?></p>
            <p><strong>Pseudo :</strong> <?php echo htmlspecialchars($user['pseudo']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['mail']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user['num']); ?></p>
            <p><strong>Adresse :</strong> <?php echo htmlspecialchars($user['adresse']); ?></p>
        </div>
        <div class="actions" style="display:flex;flex-wrap:wrap;gap:22px;justify-content:center;">
            <a href="add-article.php" class="action-btn compte-btn">Ajouter un article</a>
            <a href="#" id="show-infos" class="action-btn compte-btn">Mes infos</a>
            <a href="articles-preteur.php" class="action-btn compte-btn">Mes articles à prêter</a>
            
            <a href="messagerie.php" class="action-btn compte-btn">
                Messagerie<?php if ($nbNotif > 0): ?> (<?= $nbNotif ?>)<?php endif; ?>
            </a>
            <?php
            $stmtPreteur = $pdo->prepare('SELECT COUNT(*) FROM article WHERE id_preteur = ?');
            $stmtPreteur->execute([$user_id]);
            $isPreteur = $stmtPreteur->fetchColumn() > 0;
            if ($isPreteur): ?>
                <a href="suivre_pret.php"><button id="show-prets" class="action-btn compte-btn">Suivre un prêt</button></a>
            <?php endif; ?>
            <a href="deconnexion.php" class="action-btn compte-btn" style="background:linear-gradient(90deg,#e74c3c 0%,#e9eafc 100%);color:#fff;">Déconnexion</a>
        </div>
        <div id="modal-edit-infos" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);z-index:1000;align-items:center;justify-content:center;">
            <div style="background:rgba(255,255,255,0.98);padding:32px 24px 24px 24px;border-radius:18px;max-width:420px;width:90vw;box-shadow:0 8px 32px #4e54c81a;position:relative;animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);">
                <span id="close-edit-infos" style="position:absolute;top:12px;right:18px;cursor:pointer;font-size:28px;color:#e74c3c;font-weight:bold;">&times;</span>
                <h3 style="margin-top:0;color:#2986cc;text-align:center;font-size:1.3em;">Modifier mes informations</h3>
                <form method="post" style="margin-top:18px;">
                    <label>Nom :</label><br>
                    <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required style="width:100%;padding:8px;margin-bottom:10px;"><br>
                    <label>Prénom :</label><br>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required style="width:100%;padding:8px;margin-bottom:10px;"><br>
                    <label>Pseudo :</label><br>
                    <input type="text" name="pseudo" value="<?= htmlspecialchars($user['pseudo']) ?>" required style="width:100%;padding:8px;margin-bottom:10px;"><br>
                    <label>Email :</label><br>
                    <input type="email" name="mail" value="<?= htmlspecialchars($user['mail']) ?>" required style="width:100%;padding:8px;margin-bottom:10px;"><br>
                    <label>Téléphone :</label><br>
                    <input type="text" name="num" value="<?= htmlspecialchars($user['num']) ?>" style="width:100%;padding:8px;margin-bottom:10px;"><br>
                    <label>Adresse :</label><br>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse']) ?>" style="width:100%;padding:8px;margin-bottom:10px;"><br>
                    <button type="submit" name="edit_infos" style="background:#2986cc;color:#fff;padding:10px 22px;border:none;border-radius:6px;cursor:pointer;font-size:1.1em;">Enregistrer</button>
                </form>
            </div>
        </div>
        <script>
        const userInfos = document.getElementById('user-infos');
        const showInfosBtn = document.getElementById('show-infos');
        const closeInfos = document.getElementById('close-infos');
        showInfosBtn.addEventListener('click', function(e) {
            e.preventDefault();
            userInfos.style.display = 'block';
            showInfosBtn.style.display = 'none';
        });
        closeInfos.addEventListener('click', function() {
            userInfos.style.display = 'none';
            showInfosBtn.style.display = '';
        });
        document.getElementById('show-edit-infos').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('modal-edit-infos').style.display = 'flex';
        });
        document.getElementById('close-edit-infos').addEventListener('click', function() {
            document.getElementById('modal-edit-infos').style.display = 'none';
        });
        </script>
    </div>
    <div id="modal-prets" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:rgba(255,255,255,0.98);padding:32px 24px 24px 24px;border-radius:18px;max-width:420px;width:90vw;box-shadow:0 8px 32px #4e54c81a;position:relative;animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);">
            <span id="close-prets" style="position:absolute;top:12px;right:18px;cursor:pointer;font-size:28px;color:#e74c3c;font-weight:bold;">&times;</span>
            <h3 style="margin-top:0;color:#4e54c8;text-align:center;font-size:1.3em;">Mes prêts en cours</h3>
            <div style="margin-top:18px;">
            <?php
            $stmtPrets = $pdo->prepare('SELECT d.id, a.nom as article_nom, d.date_retrait, d.date_retour, d.id_preteur, d.id_emprunteur FROM demande d JOIN article a ON d.id_article = a.id WHERE (d.id_preteur = ? OR d.id_emprunteur = ?) AND a.etat = 3 ORDER BY d.date_retrait DESC');
            $stmtPrets->execute([$user_id, $user_id]);
            $prets = $stmtPrets->fetchAll();
            if (count($prets) === 0) {
                echo '<div style="color:#888;text-align:center;">Aucun prêt en cours à suivre.</div>';
            } else {
                foreach ($prets as $pret) {
                    echo '<div style="margin-bottom:16px;padding:10px 0;border-bottom:1px solid #eee;">';
                    echo '<div style="font-weight:bold;color:#4e54c8;">' . htmlspecialchars($pret['article_nom']) . '</div>';
                    echo '<div style="font-size:0.97em;color:#555;">Du ' . date('d/m/Y', strtotime($pret['date_retrait'])) . ' au ' . date('d/m/Y', strtotime($pret['date_retour'])) . '</div>';
                    echo '<a href="suivre_pret.php?demande=' . $pret['id'] . '" class="btn-suivi" style="margin-top:8px;display:inline-block;">Voir le suivi</a>';
                    echo '</div>';
                }
            }
            ?>
            </div>
        </div>
    </div>
</main>
<style>
@keyframes fadeInCard {
    0% { opacity:0; transform:translateY(40px) scale(0.98); }
    100% { opacity:1; transform:translateY(0) scale(1); }
}
.compte-btn:hover, .compte-btn:focus {
    background:linear-gradient(90deg,#4e54c8 0%,#8f94fb 100%);
    color:#fff;
    box-shadow:0 8px 32px #4e54c830;
    transform:scale(1.04);
    border:1px solid #4e54c8;
}
.compte-container {
    backdrop-filter:blur(2px);
}
.btn-suivi {
    display:inline-block;
    background:linear-gradient(90deg,#4e54c8 0%,#8f94fb 100%);
    color:#fff;
    padding:8px 18px;
    border-radius:8px;
    font-weight:bold;
    text-decoration:none;
    transition:background 0.3s;
    margin:6px 0;
}
.btn-suivi:hover {
    background:linear-gradient(90deg,#8f94fb 0%,#4e54c8 100%);
    color:#fff;
}
@media (max-width: 700px) {
    .compte-container {
        padding:18px 6vw;
        font-size:1em;
        margin:18px auto 0 auto;
    }
    .compte-header h2 {
        font-size:1.3em;
    }
    .actions {
        flex-direction:column;
        gap:12px;
    }
}
</style>
<?php
if (isset($_POST['edit_infos'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $pseudo = trim($_POST['pseudo']);
    $mail = trim($_POST['mail']);
    $num = trim($_POST['num']);
    $adresse = trim($_POST['adresse']);
    $stmt = $pdo->prepare('UPDATE users SET nom = ?, prenom = ?, pseudo = ?, mail = ?, num = ?, adresse = ? WHERE id = ?');
    $stmt->execute([$nom, $prenom, $pseudo, $mail, $num, $adresse, $user_id]);
    echo "<script>alert('Informations modifiées avec succès !');window.location.href='compte.php';</script>";
    exit;
}
?>
