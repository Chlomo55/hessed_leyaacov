<?php

require_once 'header.php';

// Récupérer les infos de l'utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT nom, prenom, pseudo, num, mail, adresse, ville FROM users WHERE id = :id');
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}
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
        <div class="user-infos">
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['nom']); ?></p>
            <p><strong>Prénom :</strong> <?php echo htmlspecialchars($user['prenom']); ?></p>
            <p><strong>Pseudo :</strong> <?php echo htmlspecialchars($user['pseudo']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['mail']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user['num']); ?></p>
            <p><strong>Adresse :</strong> <?php echo htmlspecialchars($user['adresse']); ?></p>
            <p><strong>Ville :</strong> <?php echo htmlspecialchars($user['ville']); ?></p>
        </div>
        <div class="actions">
            <a href="add-article.php" class="action-btn">Ajouter un article</a>
            <a href="mes_infos.php" class="action-btn">Mes infos</a>
            <a href="suivre_pret.php" class="action-btn">Suivre un prêt</a>
            <a href="deconnexion.php" class="action-btn" style="background: #e74c3c;">Déconnexion</a>
        </div>
    </div>
</body>
</html>