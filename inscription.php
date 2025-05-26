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

    if (!empty($nom) && !empty($prenom) && !empty($pseudo) && !empty($num) && !empty($address) && !empty($email) && !empty($password) && !empty($ville)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (nom, prenom, pseudo, num, mail, pass, adresse, ville) VALUES (:nom, :prenom, :pseudo, :num, :mail, :pass, :adresse, :ville)');
            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'pseudo' => $pseudo,
                'num' => $num,
                'mail' => $email,
                'pass' => password_hash($password, PASSWORD_DEFAULT),
                'adresse' => $address,
                'ville' => $ville
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

<div>
    <form action="inscription.php" method="post">
        <div>
            <h2>Inscription</h2>
            <p>Veuillez remplir le formulaire ci-dessous pour créer un compte.</p>
            <div>
                <label for="nom">Nom:</label>
                <input type="text" name="nom" id="nom" required>
            </div>
            <div>
                <label for="prenom">Prénom:</label>
                <input type="text" name="prenom" id="prenom" required>
            </div>
            <div>
                <label for="pseudo">Pseudo:</label>
                <input type="text" name="pseudo" id="pseudo" required>
            </div>
            <div>
                <label for="num">Numéro de téléphone:</label>
                <input type="tel" name="num" id="num" required>
            </div>
            <div>
                 <label for="address">Adresse:</label>
                 <input type="text" name="address" id="address" autocomplete="off" required>
                 <input type="hidden" name="ville" id="ville">
                 <div id="suggestions" style="border:1px solid #ccc; display:none; position:absolute; background:#fff; z-index:1000;"></div>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div>
                <label for="password">Mot de passe:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <input type="submit" value="S'inscrire">
            <p>Déjà inscrit ? <a href="connexion.php">Connectez-vous ici</a></p>
        </div>
    </form>
</div>

<script>
// filepath: c:\wamp64\www\Hessed_leyaacov\inscription.php
const addressInput = document.getElementById('address');
const suggestions = document.getElementById('suggestions');

addressInput.addEventListener('input', function() {
    const query = addressInput.value;
    if (query.length < 3) {
        suggestions.style.display = 'none';
        return;
    }
    fetch('https://api-adresse.data.gouv.fr/search/?q=' + encodeURIComponent(query) + '&limit=5')
        .then(response => response.json())
        .then(data => {
            suggestions.innerHTML = '';
            if (data.features.length === 0) {
                suggestions.style.display = 'none';
                return;
            }
            data.features.forEach(feature => {
                const div = document.createElement('div');
                div.textContent = feature.properties.label;
                div.style.padding = '5px';
                div.style.cursor = 'pointer';
                div.addEventListener('mousedown', function() {
                    addressInput.value = feature.properties.label;
                    document.getElementById('ville').value = feature.properties.city || '';
                    suggestions.style.display = 'none';
                });
                suggestions.appendChild(div);
            });
            suggestions.style.display = 'block';
            // Positionner la suggestion sous le champ
            const rect = addressInput.getBoundingClientRect();
            suggestions.style.left = rect.left + 'px';
            suggestions.style.top = (rect.bottom + window.scrollY) + 'px';
            suggestions.style.width = rect.width + 'px';
        });
});

// Cacher les suggestions si on clique ailleurs
document.addEventListener('click', function(e) {
    if (e.target !== addressInput) {
        suggestions.style.display = 'none';
    }
});
</script>