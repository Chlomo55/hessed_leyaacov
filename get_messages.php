<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once 'header.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['demande'])) {
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}
$user_id = $_SESSION['user_id'];
$id_demande = intval($_GET['demande']);
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
// Récupérer le prêteur, l'emprunteur et l'article pour cette demande
$stmt = $pdo->prepare('SELECT id_preteur, id_emprunteur, id_article FROM demande WHERE id = ?');
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();
if (!$demande) {
    echo json_encode(['error' => 'Demande non trouvée']);
    exit;
}
// Récupérer tous les messages entre CE prêteur et CET emprunteur pour CET article, > last_id
$stmtAll = $pdo->prepare('SELECT m.*, u.prenom, u.nom FROM messages m JOIN users u ON m.id_expediteur = u.id WHERE m.id_preteur = ? AND m.id_emprunteur = ? AND m.id_demande IN (SELECT id FROM demande WHERE id_article = ?) AND m.id > ? ORDER BY m.date_envoi ASC');
$stmtAll->execute([$demande['id_preteur'], $demande['id_emprunteur'], $demande['id_article'], $last_id]);
$messages = $stmtAll->fetchAll();
echo json_encode(['messages' => $messages]);
