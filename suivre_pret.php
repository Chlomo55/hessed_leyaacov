<script>
document.querySelectorAll('form').forEach(function(form) {
    const renduBtn = form.querySelector('button[name="rendu"]');
    if (renduBtn) {
        renduBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Crée le loader
            let loader = document.createElement('div');
            loader.className = 'loader';
            loader.style.cssText = 'display:inline-block;margin:10px auto;width:32px;height:32px;border:4px solid #ccc;border-top:4px solid #4e54c8;border-radius:50%;animation:spin 0.8s linear infinite;';
            form.appendChild(loader);

            // Simule un délai court pour le loader (300ms)
            setTimeout(function() {
                loader.remove();
                // Affiche les deux boutons
                let actionsDiv = document.createElement('div');
                actionsDiv.className = 'actions';
                actionsDiv.innerHTML = `
                    <button type="submit" name="tout_bien">Tout s'est bien passé</button>
                    <button type="button" onclick="document.getElementById('dommage-${form.pret_id.value}').classList.remove('hidden')">Un dommage à signaler</button>
                    <div id="dommage-${form.pret_id.value}" class="hidden">
                        <textarea name="message" placeholder="Expliquez le dommage"></textarea><br>
                        <input type="file" name="photo[]" multiple accept="image/*"><br>
                        <button type="submit" name="dommage">Envoyer dommage</button>
                        <button type="submit" name="demande_encaissement">Demander encaissement du chèque</button>
                    </div>
                `;
                // Vide le formulaire et ajoute les nouveaux boutons
                form.innerHTML = `<input type="hidden" name="pret_id" value="${form.pret_id.value}">`;
                form.appendChild(actionsDiv);
            }, 300);
        });
    }
});

// Animation loader
const style = document.createElement('style');
style.innerHTML = `
@keyframes spin {
    0% { transform: rotate(0deg);}
    100% { transform: rotate(360deg);}
}
.loader { }
`;
document.head.appendChild(style);
</script><?php
include_once('header.php');
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=gmah', 'root', '');

// Récupération des prêts (table prets)
$sql = "SELECT p.id, a.nom AS article, u1.nom AS preteur, p.emprunteur, p.date_debut, p.date_fin, a.id as article_id, a.etat, p.etat as pret_etat
        FROM prets p
        JOIN article a ON p.article = a.id
        JOIN users u1 ON a.id_preteur = u1.id";
$prets = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function jours_restants($date_fin) {
    $fin = new DateTime($date_fin);
    $now = new DateTime();
    $interval = $now->diff($fin);
    return $interval->invert ? 0 : $interval->days;
}

// Gestion des actions (rendu, dommage, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pret_id = $_POST['pret_id'];
    if (isset($_POST['rendu'])) {
        $pdo->prepare('UPDATE demande SET statut=1 WHERE id=?')->execute([$pret_id]); // 1 = rendu
    }
    if (isset($_POST['fin_bien'])) {
        // Passage à l'état 2 (prêt terminé parfaitement)
        $pdo->prepare('UPDATE article SET etat=2 WHERE id=(SELECT article FROM prets WHERE id=?)')->execute([$pret_id]);
        $pdo->prepare('UPDATE prets SET etat=2 WHERE id=?')->execute([$pret_id]);
        // On peut ajouter un message de confirmation ici si besoin
    }
    if (isset($_POST['fin_dommage'])) {
        $message = $_POST['message'] ?? '';
        $montant_demande = $_POST['montant_demande'] ?? 0;
        $photos = [];
        if (isset($_FILES['photo'])) {
            $upload_dir = 'uploads/';
            foreach ($_FILES['photo']['tmp_name'] as $key => $tmp_name) {
                $file_name = 'dommage_' . $pret_id . '_' . uniqid() . '_' . basename($_FILES['photo']['name'][$key]);
                if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
                    $photos[] = $file_name;
                }
            }
        }
        $photos_json = json_encode($photos);
        $pdo->prepare('UPDATE article SET etat=3 WHERE id=(SELECT article FROM prets WHERE id=?)')->execute([$pret_id]);
        $pdo->prepare('UPDATE prets SET etat=3, message=?, montant_demande=?, photos_dommage=? WHERE id=?')
            ->execute([ $message, $montant_demande, $photos_json, $pret_id ]);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi des prêts</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-bottom: 2em; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        .actions { margin-top: 10px; }
        .hidden { display: none; }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            width: 70%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #4e54c8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #3f44a3;
        }
        .checkbox-group {
            margin: 15px 0;
        }
        .checkbox-group label {
            display: block;
            margin: 10px 0;
        }
        .textarea-group {
            margin: 15px 0;
        }
        .textarea-group textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .photo-upload {
            margin: 15px 0;
        }
        .success-message {
            color: #28a745;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .error-message {
            color: #dc3545;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        input[type='number'] {
            width: 150px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<h1>Suivi des prêts</h1>
<table>
    <thead>
        <tr>
            <th>Article</th>
            <th>Prêteur</th>
            <th>Emprunteur</th>
            <th>Date début</th>
            <th>Heure début</th>
            <th>Date fin</th>
            <th>Heure fin</th>
            <th>Jours restants</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($prets as $pret): ?>
        <tr>
            <td><?= htmlspecialchars($pret['article']) ?></td>
            <td><?= htmlspecialchars($pret['preteur']) ?></td>
            <td><?= htmlspecialchars($pret['emprunteur']) ?></td>
            <td><?= date('d/m/Y', strtotime($pret['date_debut'])) ?></td>
            <td><?= date('H:i', strtotime($pret['date_debut'])) ?></td>
            <td><?= date('d/m/Y', strtotime($pret['date_fin'])) ?></td>
            <td><?= date('H:i', strtotime($pret['date_fin'])) ?></td>
            <td><?= jours_restants($pret['date_fin']) ?></td>
            <td>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="pret_id" value="<?= $pret['id'] ?>">
                    <?php if ($pret['pret_etat'] == 1): ?>
                        <button type="button" class="btn-primary" onclick="openReturnModal(<?= $pret['id'] ?>)">Mettre fin au prêt</button>
                        <div id="returnModal-<?= $pret['id'] ?>" class="modal">
                            <div class="modal-content">
                                <h3>Comment s'est passé le retour ?</h3>
                                <form method="post" enctype="multipart/form-data" class="return-form" id="returnForm-<?= $pret['id'] ?>">
                                    <input type="hidden" name="pret_id" value="<?= $pret['id'] ?>">
                                    <div class="choice-buttons">
                                        <button type="button" class="btn-primary" onclick="showGoodReturn(<?= $pret['id'] ?>)">Tout s'est bien passé</button>
                                        <button type="button" class="btn-primary" onclick="showDamageReport(<?= $pret['id'] ?>)">Un dommage à signaler</button>
                                    </div>
                                    <div id="goodReturn-<?= $pret['id'] ?>" class="hidden">
                                        <div class="checkbox-group">
                                            <label>
                                                <input type="checkbox" name="article_recupere" required> 
                                                Je certifie avoir récupéré l'article
                                            </label>
                                            <label>
                                                <input type="checkbox" name="cheque_rendu" required> 
                                                Je certifie avoir rendu le chèque de caution
                                            </label>
                                        </div>
                                        <button type="submit" name="fin_bien" class="btn-primary">Confirmer</button>
                                    </div>
                                    <div id="damageReport-<?= $pret['id'] ?>" class="hidden">
                                        <div class="textarea-group">
                                            <label>Description du dommage :</label>
                                            <textarea name="message" required placeholder="Décrivez en détail le dommage constaté"></textarea>
                                        </div>
                                        <div class="input-group">
                                            <label>Montant suggéré de prélèvement sur la caution (€) :</label>
                                            <input type="number" name="montant_demande" min="0" required>
                                        </div>
                                        <div class="photo-upload">
                                            <label>Photos du dommage (2 à 5 photos) :</label>
                                            <input type="file" name="photo[]" multiple accept="image/*" required 
                                                onchange="validatePhotos(this)" data-min="2" data-max="5">
                                            <div class="error-message hidden"></div>
                                        </div>
                                        <button type="submit" name="fin_dommage" class="btn-primary">Envoyer le signalement</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($pret['pret_etat'] == 2): ?>
                        <span>Prêt terminé, tout s'est bien passé</span>
                    <?php elseif ($pret['pret_etat'] == 3): ?>
                        <span>Prêt terminé, dommage signalé</span>
                    <?php else: ?>
                        <span>En attente de début</span>
                    <?php endif; ?>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<script>
function openReturnModal(pretId) {
    document.getElementById(`returnModal-${pretId}`).style.display = 'block';
}
function showGoodReturn(pretId) {
    document.getElementById(`goodReturn-${pretId}`).classList.remove('hidden');
    document.getElementById(`damageReport-${pretId}`).classList.add('hidden');
}
function showDamageReport(pretId) {
    document.getElementById(`damageReport-${pretId}`).classList.remove('hidden');
    document.getElementById(`goodReturn-${pretId}`).classList.add('hidden');
}
function validatePhotos(input) {
    const min = parseInt(input.dataset.min);
    const max = parseInt(input.dataset.max);
    const files = input.files;
    const errorDiv = input.nextElementSibling;
    if (files.length < min || files.length > max) {
        errorDiv.textContent = `Veuillez sélectionner entre ${min} et ${max} photos.`;
        errorDiv.classList.remove('hidden');
        input.value = '';
    } else {
        errorDiv.classList.add('hidden');
    }
}
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
document.querySelectorAll('.return-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (form.querySelector('[name="dommage"]') && this.elements['photo[]'].files.length < 2) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins 2 photos');
            return;
        }
        if (this.elements['dommage']) {
            const message = document.createElement('div');
            message.className = 'success-message';
            message.textContent = 'Votre signalement a été envoyé. Notre service traitera votre demande dans les plus brefs délais.';
            form.appendChild(message);
        }
    });
});
</script>
</body>
</html>