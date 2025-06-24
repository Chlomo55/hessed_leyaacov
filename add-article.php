<!-- AJOUTER UN ARTICLE -->
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
    $pref = isset($_POST['pref']) ? implode(', ', $_POST['pref']) : '';

    // Gestion des photos en LONGBLOB
    $photos = array_fill(1, 5, null); // Par défaut, toutes les colonnes sont null
    for ($i = 1; $i <= 5; $i++) {
        if (isset($_FILES["photo_$i"]) && $_FILES["photo_$i"]['error'] === UPLOAD_ERR_OK) {
            $photos[$i] = file_get_contents($_FILES["photo_$i"]['tmp_name']);
        }
    }

    // Insertion en base avec etat à 0
    $stmt = $pdo->prepare("INSERT INTO article (id_preteur, nom, detail, photo_1, photo_2, photo_3, photo_4, photo_5, pref, etat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bindParam(1, $id_preteur, PDO::PARAM_INT);
    $stmt->bindParam(2, $nom, PDO::PARAM_STR);
    $stmt->bindParam(3, $detail, PDO::PARAM_STR);
    for ($i = 1; $i <= 5; $i++) {
        $stmt->bindParam($i+3, $photos[$i], PDO::PARAM_LOB);
    }
    $stmt->bindParam(9, $pref, PDO::PARAM_STR);
    $stmt->execute();

    // Récupérer l'email du prêteur
    $stmt_mail = $pdo->prepare("SELECT mail FROM users WHERE id = ?");
    $stmt_mail->execute([$id_preteur]);
    $mail_preteur = $stmt_mail->fetchColumn();

    // Envoi du mail via PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Paramètres SMTP à adapter selon ton hébergeur
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // À remplacer
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

    $success = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article à prêter</title>
    <style>
        body {
            background: linear-gradient(120deg, #8f94fb 0%, #4e54c8 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .main-container {
            max-width: 520px;
            margin: 48px auto 0 auto;
            background: rgba(255,255,255,0.98);
            border-radius: 22px;
            box-shadow: 0 8px 40px rgba(78,84,200,0.18);
            padding: 44px 36px 32px 36px;
            position: relative;
        }
        h1 {
            color: #4e54c8;
            text-align: center;
            margin-bottom: 32px;
            font-size: 2.2em;
            letter-spacing: 1px;
        }
        #success-message {
            background: linear-gradient(90deg, #2ecc40 0%, #27ae60 100%);
            color: #fff;
            padding: 22px 28px 18px 28px;
            border-radius: 12px;
            margin-bottom: 22px;
            text-align: center;
            font-size: 1.18em;
            box-shadow: 0 2px 12px rgba(46,204,64,0.13);
            animation: fadeIn 0.5s;
        }
        #add-another {
            margin-top: 18px;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            font-size: 1.08em;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(78,84,200,0.10);
            transition: background 0.2s, box-shadow 0.2s;
        }
        #add-another:hover {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
            box-shadow: 0 4px 16px rgba(78,84,200,0.18);
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 22px;
            animation: fadeIn 0.7s;
        }
        label {
            color: #2d3a4b;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 1.08em;
        }
        input[type="text"], textarea {
            padding: 12px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-size: 1.08em;
            background: #f7f7fa;
            margin-bottom: 2px;
            transition: border 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 4px rgba(78,84,200,0.04);
        }
        input[type="text"]:focus, textarea:focus {
            border: 1.5px solid #4e54c8;
            outline: none;
            background: #f0f4ff;
            box-shadow: 0 2px 8px rgba(78,84,200,0.10);
        }
        textarea {
            min-height: 90px;
            resize: vertical;
        }
        .photo-div {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .photo-div label {
            margin-bottom: 0;
            font-weight: 500;
        }
        input[type="file"] {
            margin-top: 0;
            font-size: 1em;
        }
        .photo-div img {
            display: block;
            margin-top: 0;
            width: 90px;
            height: 90px;
            object-fit: cover;
            border: 2px solid #4e54c8;
            border-radius: 10px;
            background: #eee;
            box-shadow: 0 2px 8px rgba(78,84,200,0.10);
        }
        #add-photo {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 9px 22px;
            font-size: 1.05em;
            cursor: pointer;
            margin-bottom: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }
        #add-photo:disabled {
            background: #bbb;
            cursor: not-allowed;
        }
        #add-photo:hover:not(:disabled) {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
        }
        fieldset {
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            padding: 16px 22px 12px 22px;
            background: #f7f7fa;
            box-shadow: 0 1px 4px rgba(78,84,200,0.04);
        }
        legend {
            color: #4e54c8;
            font-weight: bold;
            font-size: 1.08em;
        }
        button[type="submit"] {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 14px 0;
            font-size: 1.15em;
            font-weight: bold;
            margin-top: 10px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(78,84,200,0.10);
            transition: background 0.2s, box-shadow 0.2s;
        }
        button[type="submit"]:hover {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
            box-shadow: 0 4px 16px rgba(78,84,200,0.18);
        }
        .form-row {
            display: flex;
            gap: 18px;
            width: 100%;
        }
        .form-col {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .form-col label {
            width: 100%;
        }
        @media (max-width: 700px) {
            .main-container { padding: 12px 2vw; }
            h1 { font-size: 1.3em; }
            .form-row { flex-direction: column; gap: 0; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h1>Ajouter un article à prêter</h1>
        <div id="success-message" style="display:<?php echo isset($success) ? 'block' : 'none'; ?>;">
            Article ajouté avec succès ! Un mail de confirmation vous a été envoyé.<br>
            <button id="add-another">Ajouter un autre article</button>
        </div>
        <form id="add-article-form" method="post" enctype="multipart/form-data" style="display:<?php echo isset($success) ? 'none' : 'block'; ?>;">
            <div class="form-row">
                <div class="form-col">
                    <label>Nom :<br>
                        <input type="text" name="nom" required>
                    </label>
                </div>
                <div class="form-col">
                    <label>Détail :<br>
                        <textarea name="detail" required></textarea>
                    </label>
                </div>
            </div>
            <div id="photos">
                <div class="photo-div">
                    <label>Photo 1 :
                        <input type="file" name="photo_1" accept="image/*" onchange="previewImage(this, 1)">
                        <img id="preview_1" src="" alt="Prévisualisation" style="display:none;width:90px;height:90px;object-fit:cover;border:2px solid #4e54c8;border-radius:10px;background:#eee;box-shadow:0 2px 8px rgba(78,84,200,0.10);" />
                    </label>
                </div>
            </div>
            <button type="button" id="add-photo">Ajouter une photo</button>
            <fieldset>
                <legend>Préférences de contact :</legend>
                <label><input type="checkbox" name="pref[]" value="sms"> SMS</label>
                <label><input type="checkbox" name="pref[]" value="mail"> Mail</label>
                <label><input type="checkbox" name="pref[]" value="messagerie"> Messagerie du site</label>
            </fieldset>
            <button type="submit">Ajouter l'article</button>
        </form>
    </div>

    <script>
        let photoCount = 1;
        document.getElementById('add-photo').onclick = function() {
            if (photoCount >= 5) return;
            photoCount++;
            const div = document.createElement('div');
            div.className = 'photo-div';
            div.innerHTML = `<label>Photo ${photoCount} :
                <input type="file" name="photo_${photoCount}" accept="image/*" onchange="previewImage(this, ${photoCount})">
                <img id="preview_${photoCount}" src="" alt="Prévisualisation" style="display:none;" />
            </label>`;
            document.getElementById('photos').appendChild(div);
            if (photoCount === 5) this.disabled = true;
        };

        function previewImage(input, num) {
            const file = input.files && input.files[0];
            const preview = document.getElementById('preview_' + num);
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }

        if(document.getElementById('add-another')){
            document.getElementById('add-another').onclick = function() {
                document.getElementById('success-message').style.display = 'none';
                document.getElementById('add-article-form').style.display = 'block';
            };
        }
    </script>
</body>
</html>