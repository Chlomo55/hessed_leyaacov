<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hessed LéYaacov</title>
    <link rel="stylesheet" href="style.css">
    <style>
    body {
        background:linear-gradient(120deg,#f7f8fa 0%,#e9eafc 100%);
        font-family:'Segoe UI','Roboto',Arial,sans-serif;
        color:#222;
        margin:0;
        min-height:100vh;
        transition:background 0.7s cubic-bezier(.4,2,.6,1);
    }
    header {
        background:rgba(255,255,255,0.98);
        box-shadow:0 8px 32px #4e54c81a;
        border-radius:0 0 22px 22px;
        padding:2.2rem 0 1.2rem 0;
        text-align:center;
        animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);
    }
    header h1 {
        margin:0 0 0.5rem 0;
        font-size:2.3rem;
        letter-spacing:2px;
        font-weight:700;
        font-family:'Segoe UI','Roboto',Arial,sans-serif;
        transition:color 0.2s;
    }
    header h1 a {
        color:#222;
        text-decoration:none;
        transition:color 0.2s;
    }
    header h1 a:hover {
        color:#4e54c8;
    }
    nav ul {
        list-style:none;
        padding:0;
        margin:0 auto;
        display:flex;
        justify-content:center;
        gap:2.2rem;
        flex-wrap:wrap;
        animation:fadeInCard 1.3s cubic-bezier(.4,2,.6,1);
    }
    nav ul li a {
        color:#222;
        text-decoration:none;
        font-size:1.13rem;
        padding:0.7rem 1.6rem;
        border-radius:25px;
        transition:background 0.2s, color 0.2s, box-shadow 0.2s;
        font-weight:500;
        box-shadow:0 2px 8px #e9eafc;
    }
    nav ul li a:hover, nav ul li a:focus {
        background:linear-gradient(90deg,#e9eafc 0%,#4e54c8 100%);
        color:#fff;
        box-shadow:0 8px 32px #4e54c830;
    }
    @media (max-width: 600px) {
        header h1 {
            font-size:1.3rem;
        }
        nav ul {
            gap:0.7rem;
        }
        nav ul li a {
            font-size:0.95rem;
            padding:0.4rem 0.7rem;
        }
    }
    @keyframes fadeInCard {
        0% { opacity:0; transform:translateY(-40px) scale(0.98); }
        100% { opacity:1; transform:translateY(0) scale(1); }
    }
    </style>
</head>
<?php 
require_once('pdo.php');
?>
<body>
<header>
    <h1><a href="index.php">Hessed LéYaacov</a></h1>
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
</header>
<main>