<?php 
require_once 'header.php'; // Assurez-vous que $pdo est bien défini ici

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $pseudo = $_POST['pseudo'];
    $num = $_POST['num'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $ville = $_POST['ville'] ?? '';
    $type_logement = $_POST['type_logement'] ?? 'maison';
    $etage = isset($_POST['etage']) ? $_POST['etage'] : null;
    $interphone = isset($_POST['interphone']) ? $_POST['interphone'] : null;

    if (!empty($nom) && !empty($prenom) && !empty($pseudo) && !empty($num) && !empty($address) && !empty($email) && !empty($password) && !empty($ville)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (nom, prenom, pseudo, num, mail, pass, adresse, ville, type_logement, etage, interphone) VALUES (:nom, :prenom, :pseudo, :num, :mail, :pass, :adresse, :ville, :type_logement, :etage, :interphone)');
            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'pseudo' => $pseudo,
                'num' => $num,
                'mail' => $email,
                'pass' => password_hash($password, PASSWORD_DEFAULT),
                'adresse' => $address,
                'ville' => $ville,
                'type_logement' => $type_logement,
                'etage' => $type_logement === 'appartement' ? $etage : null,
                'interphone' => $type_logement === 'appartement' ? $interphone : null
            ]);
            header('Location: compte.php');
            exit;
        } catch (PDOException $e) {
            echo "Erreur lors de l'inscription: " . $e->getMessage();
        }
    } else {
        echo "Veuillez remplir tous les champs.";
    }
}
?>
<main class="register-main" style="background:linear-gradient(120deg,#f7f8fa 0%,#e9eafc 100%);min-height:100vh;display:flex;justify-content:center;align-items:center;">
    <section class="register-section" style="width:100vw;display:flex;justify-content:center;align-items:center;min-height:80vh;">
        <form action="inscription.php" method="post" class="register-card" style="background:rgba(255,255,255,0.98);padding:32px 6vw 28px 6vw;border-radius:18px;box-shadow:0 8px 32px #4e54c81a;max-width:420px;width:100%;display:flex;flex-direction:column;align-items:center;animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);">
            <h2 style="color:#222;margin-bottom:18px;font-size:2em;font-weight:700;font-family:'Segoe UI','Roboto',Arial,sans-serif;letter-spacing:1px;">Inscription</h2>
            <p class="register-desc" style="color:#888;margin-bottom:18px;text-align:center;">Veuillez remplir le formulaire ci-dessous pour créer un compte.</p>
            <div class="register-group" style="width:100%;margin-bottom:10px;">
                <label for="nom" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Nom :</label>
                <input type="text" name="nom" id="nom" required class="register-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
            </div>
            <div class="register-group" style="width:100%;margin-bottom:10px;">
                <label for="prenom" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Prénom :</label>
                <input type="text" name="prenom" id="prenom" required class="register-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
            </div>
            <div class="register-group" style="width:100%;margin-bottom:10px;">
                <label for="pseudo" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Pseudo :</label>
                <input type="text" name="pseudo" id="pseudo" required class="register-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
            </div>
            <div class="register-group" style="width:100%;margin-bottom:10px;">
                <label for="num" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Numéro de téléphone :</label>
                <input type="tel" name="num" id="num" required class="register-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
            </div>
            <div class="register-group" style="width:100%;margin-bottom:10px;position:relative;">
                <label for="address" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Adresse :</label>
                <input type="text" name="address" id="address" autocomplete="off" required class="register-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
                <div id="suggestions" class="register-suggestions"></div>
            </div>
            <div class="register-group" style="width:100%;margin-bottom:10px;">
                <label for="ville" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Ville :</label>
                <input type="text" name="ville" id="ville" required class="register-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
                <div id="ville-alert" class="ville-alert" style="display:none;"></div>
            </div>
            <div class="register-group" id="type-logement-group" style="width:100%;margin-bottom:10px;">
                <label for="type-logement" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Type de logement :</label>
                <div class="radio-group" style="display:flex;gap:24px;margin-top:6px;margin-bottom:2px;">
                    <label style="font-weight:500;color:#2d3a4b;font-size:1em;cursor:pointer;"><input type="radio" name="type_logement" value="maison" checked> Maison</label>
                    <label style="font-weight:500;color:#2d3a4b;font-size:1em;cursor:pointer;"><input type="radio" name="type_logement" value="appartement"> Appartement</label>
                </div>
            </div>
            <div id="appartement-fields" style="display:none;">
                <div class="register-group" style="width:100%;margin-bottom:10px;">
                    <label for="etage" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Étage :</label>
                    <input type="text" name="etage" id="etage" class="register-input" placeholder="Ex : 2, RDC, etc." style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
                </div>
                <div class="register-group" style="width:100%;margin-bottom:10px;">
                    <label for="interphone" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Nom sur qui sonner ou code de l'interphone :</label>
                    <input type="text" name="interphone" id="interphone" class="register-input" placeholder="Nom ou code" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
                </div>
            </div>
            <div class="register-group" style="width:100%;margin-bottom:10px;">
                <label for="email" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Email :</label>
                <input type="email" name="email" id="email" required class="register-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
            </div>
            <div class="register-group" style="width:100%;margin-bottom:10px;">
                <label for="password" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Mot de passe :</label>
                <input type="password" name="password" id="password" required class="register-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
            </div>
            <button type="submit" class="register-btn" style="background:linear-gradient(90deg,#e9eafc 0%,#4e54c8 100%);color:#222;border:none;border-radius:10px;padding:14px 0;font-size:1.13em;font-weight:bold;margin-top:10px;cursor:pointer;box-shadow:0 2px 8px #e9eafc;transition:all 0.25s cubic-bezier(.4,2,.6,1);width:100%;">S'inscrire</button>
            <p class="register-login" style="margin-top:18px;color:#555;font-size:1em;text-align:center;">Déjà inscrit ? <a href="connexion.php" style="color:#4e54c8;text-decoration:underline;font-weight:bold;">Connectez-vous</a></p>
        </form>
    </section>
</main>
<style>
@keyframes fadeInCard {
    0% { opacity:0; transform:translateY(40px) scale(0.98); }
    100% { opacity:1; transform:translateY(0) scale(1); }
}
.register-card {
    backdrop-filter:blur(2px);
}
.register-btn:hover, .register-btn:focus {
    background:linear-gradient(90deg,#4e54c8 0%,#8f94fb 100%);
    color:#fff;
    box-shadow:0 8px 32px #4e54c830;
    transform:scale(1.04);
    border:1px solid #4e54c8;
}
@media (max-width: 700px) {
    .register-card {
        padding:18px 2vw;
        font-size:1em;
        min-width:unset;
        width:100vw;
        border-radius:0;
        box-shadow:none;
    }
    .register-card h2 {
        font-size:1.3em;
    }
    .register-input {
        font-size:1em;
        padding:10px 8px;
    }
    .register-btn {
        font-size:1em;
        padding:12px 0;
    }
}
</style>
<?php
// ...existing code...
?>
<script>
const addressInput = document.getElementById('address');
const suggestions = document.getElementById('suggestions');
const villeInput = document.getElementById('ville');
const villeAlert = document.getElementById('ville-alert');
const typeMaison = document.querySelector('input[name="type_logement"][value="maison"]');
const typeAppartement = document.querySelector('input[name="type_logement"][value="appartement"]');
const appartementFields = document.getElementById('appartement-fields');
const typeLogementGroup = document.getElementById('type-logement-group');

// Affichage dynamique des champs appartement
[typeMaison, typeAppartement].forEach(radio => {
    radio.addEventListener('change', function() {
        if (typeAppartement.checked) {
            appartementFields.style.display = 'block';
        } else {
            appartementFields.style.display = 'none';
        }
    });
});

addressInput.addEventListener('input', function() {
    const query = addressInput.value.trim();
    if (query.length < 1) {
        suggestions.innerHTML = '';
        suggestions.style.display = 'none';
        return;
    }
    fetch('https://api-adresse.data.gouv.fr/search/?q=' + encodeURIComponent(query) + '&limit=5')
        .then(response => response.json())
        .then(data => {
            suggestions.innerHTML = '';
            if (!data.features || data.features.length === 0) {
                suggestions.style.display = 'none';
                return;
            }
            data.features.forEach(feature => {
                const div = document.createElement('div');
                div.textContent = feature.properties.label;
                div.className = 'suggestion-item';
                div.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    // Remplir adresse avec numéro + rue
                    let adresse = '';
                    if (feature.properties.housenumber) {
                        adresse += feature.properties.housenumber + ' ';
                    }
                    adresse += feature.properties.street || feature.properties.name || feature.properties.label;
                    addressInput.value = adresse;
                    // Remplir ville avec code postal + ville
                    let ville = '';
                    if (feature.properties.postcode) {
                        ville += feature.properties.postcode + ' ';
                    }
                    ville += feature.properties.city || '';
                    villeInput.value = ville.trim();
                    suggestions.innerHTML = '';
                    suggestions.style.display = 'none';
                    if (!villeInput.value) {
                        villeAlert.textContent = 'Veuillez sélectionner une adresse contenant une ville.';
                        villeAlert.style.display = 'block';
                    } else {
                        villeAlert.style.display = 'none';
                    }
                    typeLogementGroup.style.display = 'block';
                });
                suggestions.appendChild(div);
            });
            suggestions.style.display = 'block';
        })
        .catch(() => {
            suggestions.innerHTML = '';
            suggestions.style.display = 'none';
        });
});

// Cacher les suggestions si on clique ailleurs
addressInput.addEventListener('blur', function() {
    setTimeout(() => { suggestions.style.display = 'none'; }, 150);
});
document.addEventListener('click', function(e) {
    if (e.target !== addressInput) {
        suggestions.style.display = 'none';
    }
});
// Masquer le choix maison/appartement tant qu'une adresse n'est pas sélectionnée
if (!villeInput.value) {
    typeLogementGroup.style.display = 'none';
}
addressInput.addEventListener('focus', function() {
    if (!villeInput.value) {
        typeLogementGroup.style.display = 'none';
    }
});
// Vérification à la soumission
const form = document.querySelector('.register-card');
form.addEventListener('submit', function(e) {
    if (!villeInput.value) {
        villeAlert.textContent = 'Le champ ville est obligatoire.';
        villeAlert.style.display = 'block';
        villeInput.focus();
        e.preventDefault();
    } else {
        villeAlert.style.display = 'none';
    }
});
</script>