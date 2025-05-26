<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hessed LéYaacov</title>
</head>
<?php
$pdo = new PDO('mysql:host=localhost;dbname=gmah', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
<body>
<header>
    <h1>Hessed LéYaacov</h1>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="articles.php">Articles</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="compte.php">Mon compte</a></li>
            <?php else: ?>
                <li><a href="connexion.php">Connexion</a></li>
            <?php endif; ?>
                        
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>