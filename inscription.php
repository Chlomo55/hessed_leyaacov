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

<div class="register-section">
    <form action="inscription.php" method="post" class="register-card">
        <h2>Inscription</h2>
        <p class="register-desc">Veuillez remplir le formulaire ci-dessous pour créer un compte.</p>
        <div class="register-group">
            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" required class="register-input">
        </div>
        <div class="register-group">
            <label for="prenom">Prénom :</label>
            <input type="text" name="prenom" id="prenom" required class="register-input">
        </div>
        <div class="register-group">
            <label for="pseudo">Pseudo :</label>
            <input type="text" name="pseudo" id="pseudo" required class="register-input">
        </div>
        <div class="register-group">
            <label for="num">Numéro de téléphone :</label>
            <input type="tel" name="num" id="num" required class="register-input">
        </div>
        <div class="register-group" style="position:relative;">
            <label for="address">Adresse :</label>
            <input type="text" name="address" id="address" autocomplete="off" required class="register-input">
            <div id="suggestions" class="register-suggestions"></div>
        </div>
        <div class="register-group">
            <label for="ville">Ville :</label>
            <input type="text" name="ville" id="ville" required class="register-input">
            <div id="ville-alert" class="ville-alert" style="display:none;"></div>
        </div>
        <div class="register-group" id="type-logement-group">
            <label for="type-logement">Type de logement :</label>
            <div class="radio-group">
                <label><input type="radio" name="type_logement" value="maison" checked> Maison</label>
                <label><input type="radio" name="type_logement" value="appartement"> Appartement</label>
            </div>
        </div>
        <div id="appartement-fields" style="display:none;">
            <div class="register-group">
                <label for="etage">Étage :</label>
                <input type="text" name="etage" id="etage" class="register-input" placeholder="Ex : 2, RDC, etc.">
            </div>
            <div class="register-group">
                <label for="interphone">Nom sur qui sonner ou code de l'interphone :</label>
                <input type="text" name="interphone" id="interphone" class="register-input" placeholder="Nom ou code">
            </div>
        </div>
        <div class="register-group">
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required class="register-input">
        </div>
        <div class="register-group">
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required class="register-input">
        </div>
        <button type="submit" class="register-btn">S'inscrire</button>
        <p class="register-login">Déjà inscrit ? <a href="connexion.php">Connectez-vous ici</a></p>
    </form>
</div>

<style>
body {
    background: linear-gradient(120deg, #8f94fb 0%, #4e54c8 100%);
    min-height: 100vh;
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.register-section {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
}
.register-card {
    background: #fff;
    padding: 38px 32px 28px 32px;
    border-radius: 18px;
    box-shadow: 0 6px 32px rgba(78,84,200,0.13);
    min-width: 340px;
    max-width: 95vw;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5em;
}
.register-card h2 {
    color: #4e54c8;
    margin-bottom: 8px;
    font-size: 2em;
}
.register-desc {
    color: #555;
    margin-bottom: 18px;
    text-align: center;
}
.register-group {
    width: 100%;
    display: flex;
    flex-direction: column;
    margin-bottom: 10px;
}
label {
    color: #2d3a4b;
    font-weight: 500;
    margin-bottom: 2px;
}
.register-input {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1em;
    background: #f7f7fa;
    margin-bottom: 2px;
    transition: border 0.2s;
}
.register-input:focus {
    border: 1.5px solid #4e54c8;
    outline: none;
    background: #f0f4ff;
}
.register-btn {
    background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px 0;
    font-size: 1.1em;
    font-weight: bold;
    margin-top: 10px;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(78,84,200,0.10);
    transition: background 0.2s, box-shadow 0.2s;
    width: 100%;
}
.register-btn:hover {
    background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
    box-shadow: 0 4px 16px rgba(78,84,200,0.18);
}
.register-login {
    margin-top: 18px;
    color: #555;
    font-size: 1em;
    text-align: center;
}
.register-login a {
    color: #4e54c8;
    text-decoration: underline;
    font-weight: bold;
}
.register-suggestions {
    border:1px solid #ccc;
    display:none;
    position:absolute;
    background:#fff;
    z-index:1000;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 8px rgba(78,84,200,0.10);
    max-height: 180px;
    overflow-y: auto;
    left: 0;
    top: 100%;
    width: 100%;
}
.register-suggestions div {
    padding: 8px 12px;
    cursor: pointer;
    transition: background 0.15s;
}
.register-suggestions div:hover {
    background: #f0f4ff;
}
.ville-alert {
    color: #e74c3c;
    background: #ffeaea;
    border-radius: 8px;
    padding: 7px 12px;
    margin-top: 4px;
    font-size: 0.98em;
    font-weight: 500;
    display: block;
}
.radio-group {
    display: flex;
    gap: 24px;
    margin-top: 6px;
    margin-bottom: 2px;
}
.radio-group label {
    font-weight: 500;
    color: #2d3a4b;
    font-size: 1em;
    cursor: pointer;
}
#appartement-fields {
    margin-bottom: 10px;
    animation: fadeIn 0.3s;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (max-width: 600px) {
    .register-card { min-width: unset; width: 98vw; padding: 18px 4vw; }
}
</style>

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