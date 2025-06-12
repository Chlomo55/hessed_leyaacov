<?php
require_once 'header.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}
$user_id = $_SESSION['user_id'];
$id_demande = isset($_POST['id_demande']) ? intval($_POST['id_demande']) : 0;
if ($id_demande <= 0 || !isset($_FILES['audio'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}
// Vérifier que l'utilisateur est bien concerné par la demande
$stmt = $pdo->prepare('SELECT id_preteur, id_emprunteur FROM demande WHERE id = ?');
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();
if (!$demande || ($demande['id_preteur'] != $user_id && $demande['id_emprunteur'] != $user_id)) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}
// Sauvegarder le fichier audio
$dir = 'uploads/audio/';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$ext = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);
$filename = uniqid('audio_') . '.webm';
$filepath = $dir . $filename;
if (move_uploaded_file($_FILES['audio']['tmp_name'], $filepath)) {
    // Enregistrer le message audio dans la table messages
    $stmt = $pdo->prepare('INSERT INTO messages (id_demande, id_preteur, id_emprunteur, id_expediteur, message, audio, date_envoi) VALUES (?, ?, ?, ?, NULL, ?, NOW())');
    $stmt->execute([$id_demande, $demande['id_preteur'], $demande['id_emprunteur'], $user_id, $filename]);
    echo json_encode(['success' => true, 'audio' => $filename]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur upload']);
}
