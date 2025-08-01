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
    $caution = isset($_POST['caution']) ? trim($_POST['caution']) : null;
    $heure_retrait_debut = $_POST['heure_retrait_debut'] ?? '';
    $heure_retrait_fin = $_POST['heure_retrait_fin'] ?? '';
    $heure_retour_debut = $_POST['heure_retour_debut'] ?? '';
    $heure_retour_fin = $_POST['heure_retour_fin'] ?? '';
    if (!preg_match('/^\d+$/', $caution)) {
        $error = "La caution doit être un nombre uniquement.";
    } else {
        // Gestion des photos : on enregistre le nom du fichier et on déplace le fichier dans uploads/
        $photos = array_fill(1, 5, null); // Par défaut, toutes les colonnes sont null
        $nb_photos = 0;
        for ($i = 1; $i <= 5; $i++) {
            if (isset($_FILES["photo_$i"]) && $_FILES["photo_$i"]['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES["photo_$i"]['name'], PATHINFO_EXTENSION);
                $unique_name = 'photo_' . $i . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES["photo_$i"]['tmp_name'], __DIR__ . '/uploads/' . $unique_name);
                $photos[$i] = $unique_name;
                $nb_photos++;
            }
        }

        // Vérification : au moins 2 photos requises
        if ($nb_photos < 2) {
            $error = "Veuillez sélectionner au moins 2 images pour ajouter un article.";
        } else {
            // Insertion en base avec etat à 0
            $stmt = $pdo->prepare("INSERT INTO article (id_preteur, nom, detail, photo_1, photo_2, photo_3, photo_4, photo_5, pref, caution, etat, heure_retrait_debut, heure_retrait_fin, heure_retour_debut, heure_retour_fin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)");
            $stmt->bindParam(1, $id_preteur, PDO::PARAM_INT);
            $stmt->bindParam(2, $nom, PDO::PARAM_STR);
            $stmt->bindParam(3, $detail, PDO::PARAM_STR);
            for ($i = 1; $i <= 5; $i++) {
                $stmt->bindParam($i+3, $photos[$i], PDO::PARAM_STR);
            }
            $stmt->bindParam(9, $pref, PDO::PARAM_STR);
            $stmt->bindParam(10, $caution, PDO::PARAM_INT);
            $stmt->bindParam(11, $heure_retrait_debut, PDO::PARAM_STR);
            $stmt->bindParam(12, $heure_retrait_fin, PDO::PARAM_STR);
            $stmt->bindParam(13, $heure_retour_debut, PDO::PARAM_STR);
            $stmt->bindParam(14, $heure_retour_fin, PDO::PARAM_STR);
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
                $mail->Username = 'chlomo.freoua@gmail.com';
                $mail->Password = 'bpwotttwhkaqmmkl';
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
    }
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
            max-width: 540px;
            margin: 48px auto 0 auto;
            background: rgba(255,255,255,0.99);
            border-radius: 28px;
            box-shadow: 0 10px 48px rgba(78,84,200,0.22);
            padding: 48px 38px 36px 38px;
            position: relative;
        }
        h1 {
            color: #4e54c8;
            text-align: center;
            margin-bottom: 36px;
            font-size: 2.3em;
            letter-spacing: 1.5px;
            font-weight: 800;
        }
        #success-message {
            background: linear-gradient(90deg, #2ecc40 0%, #27ae60 100%);
            color: #fff;
            padding: 24px 30px 20px 30px;
            border-radius: 14px;
            margin-bottom: 24px;
            text-align: center;
            font-size: 1.22em;
            box-shadow: 0 2px 14px rgba(46,204,64,0.15);
            animation: fadeIn 0.5s;
        }
        #add-another {
            margin-top: 20px;
            padding: 13px 32px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            font-size: 1.13em;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(78,84,200,0.13);
            transition: background 0.2s, box-shadow 0.2s;
        }
        #add-another:hover {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
            box-shadow: 0 4px 18px rgba(78,84,200,0.20);
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 24px;
            animation: fadeIn 0.7s;
        }
        label {
            color: #2d3a4b;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 1.10em;
        }
        input[type="text"], textarea {
            padding: 13px 15px;
            border: 1.7px solid #d1d5db;
            border-radius: 12px;
            font-size: 1.10em;
            background: #f7f7fa;
            margin-bottom: 2px;
            transition: border 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 5px rgba(78,84,200,0.05);
        }
        input[type="text"]:focus, textarea:focus {
            border: 1.7px solid #4e54c8;
            outline: none;
            background: #f0f4ff;
            box-shadow: 0 2px 10px rgba(78,84,200,0.13);
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        .photo-div {
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 18px;
            position: relative;
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
        .remove-photo {
            position: absolute;
            right: -18px;
            top: 50%;
            transform: translateY(-50%);
            background: #ff4d4f;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(255,77,79,0.13);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .remove-photo:hover {
            background: #d7263d;
        }
        #add-photo {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 11px 28px;
            font-size: 1.10em;
            cursor: pointer;
            margin-bottom: 10px;
            font-weight: 700;
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
            border: 1.7px solid #d1d5db;
            border-radius: 12px;
            padding: 18px 24px 14px 24px;
            background: #f7f7fa;
            box-shadow: 0 1px 5px rgba(78,84,200,0.05);
        }
        legend {
            color: #4e54c8;
            font-weight: bold;
            font-size: 1.10em;
        }
        button[type="submit"] {
            background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px 0;
            font-size: 1.18em;
            font-weight: bold;
            margin-top: 12px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(78,84,200,0.13);
            transition: background 0.2s, box-shadow 0.2s;
        }
        button[type="submit"]:hover {
            background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
            box-shadow: 0 4px 18px rgba(78,84,200,0.20);
        }
        .form-row {
            display: flex;
            gap: 20px;
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
        .error-message {
            color: #d7263d;
            background: #ffeaea;
            border: 1px solid #ffb3b3;
            border-radius: 8px;
            padding: 8px 14px;
            margin-bottom: 10px;
            font-size: 1em;
            display: none;
        }
        @media (max-width: 700px) {
            .main-container { padding: 14px 2vw; }
            h1 { font-size: 1.4em; }
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
        <div id="php-photo-error" class="error-message" style="display:<?php echo isset($error) ? 'block' : 'none'; ?>;">
            <?php echo isset($error) ? $error : ''; ?>
        </div>
        <form id="add-article-form" method="post" enctype="multipart/form-data" style="display:<?php echo isset($success) ? 'none' : 'block'; ?>;">
            <div class="form-row">
                <div class="form-col">
                    <label>Article :<br>
                        <input type="text" name="nom" required>
                    </label>
                </div>
                <div class="form-col">
                    <label>Description :<br>
                        <textarea name="detail" required></textarea>
                    </label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <label>Caution (€) :<br>
                        <input type="text" name="caution" id="caution" required pattern="\d+" maxlength="8" inputmode="numeric" autocomplete="off">
                    </label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <label>Plage horaire souhaitée pour le retrait :<br>
                        <input type="time" name="heure_retrait_debut" required> à <input type="time" name="heure_retrait_fin" required>
                    </label>
                </div>
                <div class="form-col">
                    <label>Plage horaire souhaitée pour le retour :<br>
                        <input type="time" name="heure_retour_debut" required> à <input type="time" name="heure_retour_fin" required>
                    </label>
                </div>
            </div>
            <div id="photos-info" style="margin-bottom:10px; color:#4e54c8; font-weight:600; font-size:1.08em; background:#f0f4ff; border-radius:8px; padding:8px 14px;">
                ⚠️ Veuillez ajouter au minimum <b>2 photos</b> pour valider votre article.
            </div>
            <div id="photos">
                <div class="photo-div">
                    <label>Photo 1 :
                        <input type="file" name="photo_1" accept="image/*" onchange="previewImage(this, 1)">
                        <img id="preview_1" src="" alt="Prévisualisation" style="display:none;width:90px;height:90px;object-fit:cover;border:2px solid #4e54c8;border-radius:10px;background:#eee;box-shadow:0 2px 8px rgba(78,84,200,0.10);" />
                    </label>
                </div>
                <div class="photo-div">
                    <label>Photo 2 :
                        <input type="file" name="photo_2" accept="image/*" onchange="previewImage(this, 2)">
                        <img id="preview_2" src="" alt="Prévisualisation" style="display:none;width:90px;height:90px;object-fit:cover;border:2px solid #4e54c8;border-radius:10px;background:#eee;box-shadow:0 2px 8px rgba(78,84,200,0.10);" />
                    </label>
                </div>
            </div>
            <div id="photo-error" class="error-message"></div>
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
        let photoCount = 2;
        let maxPhotos = 5;
        let photoNumbers = [1,2]; // Numéros utilisés

        function getNextPhotoNumber() {
            for (let i = 1; i <= maxPhotos; i++) {
                if (!photoNumbers.includes(i)) return i;
            }
            return null;
        }

        function reorderPhotoFields() {
            const photosDiv = document.getElementById('photos');
            const photoDivs = Array.from(photosDiv.querySelectorAll('.photo-div'));
            photoNumbers = [];
            photoDivs.forEach((div, idx) => {
                const num = idx + 1;
                // Récupérer l'input et l'image existants
                const input = div.querySelector('input[type="file"]');
                const img = div.querySelector('img');
                // Mettre à jour le label
                const label = div.querySelector('label');
                label.childNodes[0].nodeValue = `Photo ${num} :`;
                // Mettre à jour le nom et l'attribut onchange de l'input
                input.name = `photo_${num}`;
                input.setAttribute('onchange', `previewImage(this, ${num})`);
                // Mettre à jour l'id de l'image
                img.id = `preview_${num}`;
                // Mettre à jour l'attribut alt
                img.alt = 'Prévisualisation';
                // Mettre à jour le bouton croix
                const btn = div.querySelector('.remove-photo');
                if (btn) {
                    btn.onclick = function() {
                        if (document.querySelectorAll('.photo-div').length > 2) {
                            div.remove();
                            reorderPhotoFields();
                            document.getElementById('add-photo').disabled = false;
                        }
                    };
                }
                photoNumbers.push(num);
            });
            photoCount = photoDivs.length;
            document.getElementById('add-photo').disabled = (photoCount >= maxPhotos);
        }

        // Ajout d'un champ photo sans toucher aux autres
        function addPhotoField(num) {
            const div = document.createElement('div');
            div.className = 'photo-div';
            div.innerHTML = `<label>Photo ${num} :\n                <input type="file" name="photo_${num}" accept="image/*" onchange="previewImage(this, ${num})">\n                <img id="preview_${num}" src="" alt="Prévisualisation" style="display:none;width:90px;height:90px;object-fit:cover;border:2px solid #4e54c8;border-radius:10px;background:#eee;box-shadow:0 2px 8px rgba(78,84,200,0.10);" />`;
            // Ajout de la croix si plus de 2 champs
            if (photoCount >= 2) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'remove-photo';
                btn.innerHTML = '&times;';
                btn.onclick = function() {
                    if (document.querySelectorAll('.photo-div').length > 2) {
                        div.remove();
                        reorderPhotoFields();
                        document.getElementById('add-photo').disabled = false;
                    }
                };
                div.appendChild(btn);
            }
            document.getElementById('photos').appendChild(div);
            photoNumbers.push(num);
            photoCount++;
            if (photoCount >= maxPhotos) document.getElementById('add-photo').disabled = true;
            reorderPhotoFields();
        }

        // Initialisation : 2 champs
        window.addEventListener('DOMContentLoaded', function() {
            photoCount = document.querySelectorAll('.photo-div').length;
            photoNumbers = Array.from({length: photoCount}, (_, i) => i+1);
            document.getElementById('add-photo').disabled = (photoCount >= maxPhotos);
            reorderPhotoFields();
        });

        document.getElementById('add-photo').onclick = function() {
            if (photoCount >= maxPhotos) return;
            const num = photoCount + 1;
            addPhotoField(num);
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

        // Validation des champs photo à la soumission
        // On exige au moins 2 champs photos présents et remplis
        document.getElementById('add-article-form').onsubmit = function(e) {
            let error = '';
            const photoDivs = Array.from(document.getElementById('photos').querySelectorAll('.photo-div'));
            for (let i = 0; i < photoDivs.length; i++) {
                const input = photoDivs[i].querySelector('input[type="file"]');
                if (!input.value) {
                    error = `Veuillez sélectionner une image pour le champ Photo ${i+1}.`;
                    break;
                }
            }
            if (error) {
                document.getElementById('photo-error').innerText = error;
                document.getElementById('photo-error').style.display = 'block';
                e.preventDefault();
            } else {
                document.getElementById('photo-error').style.display = 'none';
            }
        };

        if(document.getElementById('add-another')){
            document.getElementById('add-another').onclick = function() {
                document.getElementById('success-message').style.display = 'none';
                document.getElementById('add-article-form').style.display = 'block';
            };
        }
        // Initialisation : ajoute la croix si plus d'un champ
        reorderPhotoFields();

        // Blocage des lettres dans le champ caution
        const cautionInput = document.getElementById('caution');
        cautionInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^\d]/g, '');
        });
    </script>
</body>
</html>