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
    <div class="compte-container">
        <div class="compte-header">
            <h2>Bienvenue, <?php echo htmlspecialchars($user['prenom']); ?> !</h2>
            <p>Votre espace personnel</p>
        </div>
        <div class="user-infos" id="user-infos" style="display:none; position:relative;">
            <span id="close-infos" style="position:absolute;top:10px;right:10px;cursor:pointer;font-size:22px;color:#e74c3c;font-weight:bold;">&times;</span>
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['nom']); ?></p>
            <p><strong>Prénom :</strong> <?php echo htmlspecialchars($user['prenom']); ?></p>
            <p><strong>Pseudo :</strong> <?php echo htmlspecialchars($user['pseudo']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['mail']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user['num']); ?></p>
            <p><strong>Adresse :</strong> <?php echo htmlspecialchars($user['adresse']); ?></p>
        </div>
        <div class="actions">
            <a href="add-article.php" class="action-btn">Ajouter un article</a>
            <a href="#" id="show-infos" class="action-btn">Mes infos</a>
            <a href="articles-preteur.php" class="action-btn">Mes articles à prêter</a>
            <a href="messagerie.php" class="action-btn" style="position:relative;">
                Messagerie
                <?php if ($nbNotif > 0): ?>
                    <span style="position:absolute;top:-8px;right:-8px;background:#e74c3c;color:#fff;border-radius:50%;padding:4px 10px;font-size:0.95em;">+<?= $nbNotif ?></span>
                <?php endif; ?>
            </a>
            <?php
            // Vérifier si l'utilisateur est prêteur (a-t-il au moins un article à prêter ?)
            $stmtPreteur = $pdo->prepare('SELECT COUNT(*) FROM article WHERE id_preteur = ?');
            $stmtPreteur->execute([$user_id]);
            $isPreteur = $stmtPreteur->fetchColumn() > 0;
            if ($isPreteur): ?>
                <a href="suivre_pret.php" class="action-btn">Suivre un prêt</a>
            <?php endif; ?>
            <a href="deconnexion.php" class="action-btn" style="background: #e74c3c;">Déconnexion</a>
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
        </script>
    </div>
</body>
</html>