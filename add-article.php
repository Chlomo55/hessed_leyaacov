<?php
// Inclure PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Assure-toi que PHPMailer est installé via Composer
include_once('header.php');  // Inclut la connexion MySQL et autres éléments nécessaires

if (!isset($_SESSION['user_id'])) {
    echo "<p>Vous devez être connecté pour ajouter un article.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_preteur = intval($_SESSION['user_id']);
    $nom = trim($_POST['nom']);
    $detail = trim($_POST['detail']);
    $pref = isset($_POST['pref']) ? implode(',', $_POST['pref']) : '';

    // Gestion des photos
    $photos = array_fill(1, 5, null);
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }
    for ($i = 1; $i <= 5; $i++) {
        if (isset($_FILES["photo_$i"]) && $_FILES["photo_$i"]['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES["photo_$i"]['name'], PATHINFO_EXTENSION));
            $filename = uniqid("photo_{$i}_") . '.' . $ext;
            $destination = "uploads/$filename";
            if (move_uploaded_file($_FILES["photo_$i"]['tmp_name'], $destination)) {
                $photos[$i] = $filename;
            }
        }
    }

    // Insertion en base avec etat à 0
    $stmt = $pdo->prepare("INSERT INTO article (id_preteur, nom, detail, photo_1, photo_2, photo_3, photo_4, photo_5, pref, etat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->execute([
        $id_preteur,
        $nom,
        $detail,
        $photos[1],
        $photos[2],
        $photos[3],
        $photos[4],
        $photos[5],
        $pref
    ]);
        "issssssss",
        $id_preteur,
        $nom,
        $detail,
        $photos[1],
        $photos[2],
        $photos[3],
        $photos[4],
        $photos[5],
        $pref
    );
    $stmt->execute();

    // Récupérer l'email du prêteur
    $stmt_mail = $conn->prepare("SELECT mail FROM users WHERE id = ?");
    $stmt_mail->bind_param("i", $id_preteur);
    $stmt_mail->execute();
    $stmt_mail->bind_result($mail_preteur);
    $stmt_mail->fetch();
    $stmt_mail->close();

    // Envoi du mail via PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Paramètres SMTP à adapter selon ton hébergeur
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // À remplacer
        $mail->SMTPAuth = true;
        $mail->Username = 'chlomo.freoua@gmail.com'; // À remplacer
        $mail->Password = 'qbbnlygeawmdrsto'; // À remplacer
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@tonsite.com', 'Hessed Leyaacov');
        $mail->addAddress($mail_preteur);

        $mail->isHTML(true);
        $mail->Subject = 'Votre article a bien été soumis';
        $mail->Body = "Bonjour,<br><br>Votre article <b>$nom</b> a bien été envoyé et est en attente de validation.<br><br>L'équipe Hessed Leyaacov.";

        $mail->send();
    } catch (Exception $e) {
        // Optionnel : afficher une erreur ou loguer
    }

    echo "<p>Article ajouté avec succès ! Un mail de confirmation vous a été envoyé.</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article à prêter</title>
    <style>
        .photo-div { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Ajouter un article à prêter</h1>
    <form method="post" enctype="multipart/form-data">
        <label>Nom :<br>
            <input type="text" name="nom" required>
        </label><br><br>
        <label>Détail :<br>
            <textarea name="detail" required></textarea>
        </label><br><br>
        <div id="photos">
            <div class="photo-div">
                <label>Photo 1 :
                    <input type="file" name="photo_1" accept="image/*">
                </label>
            </div>
        </div>
        <button type="button" id="add-photo">Ajouter une photo</button><br><br>
        <fieldset>
            <legend>Préférences de contact :</legend>
            <label><input type="checkbox" name="pref[]" value="sms"> SMS</label>
            <label><input type="checkbox" name="pref[]" value="mail"> Mail</label>
            <label><input type="checkbox" name="pref[]" value="messagerie"> Messagerie du site</label>
        </fieldset><br>
        <button type="submit">Ajouter l'article</button>
    </form>

    <script>
        let photoCount = 1;
        document.getElementById('add-photo').onclick = function() {
            if (photoCount >= 5) return;
            photoCount++;
            const div = document.createElement('div');
            div.className = 'photo-div';
            div.innerHTML = `<label>Photo ${photoCount} :
                <input type="file" name="photo_${photoCount}" accept="image/*">
            </label>`;
            document.getElementById('photos').appendChild(div);
            if (photoCount === 5) this.disabled = true;
        };
    </script>
</body>
</html>