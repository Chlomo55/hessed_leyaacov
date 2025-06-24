<!-- ESPACE DE CONNEXION QUI REDIRIGE VERS LE COMPTE -->

<?php 
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE mail = :mail');
        $stmt->execute(['mail' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['pass'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            header('Location: compte.php');
            exit;
        } else {
            $error = 'Identifiants incorrects.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<section class="login-section">
    <div class="login-card">
        <h2>Connexion</h2>
        <?php if (isset($error)): ?>
            <p class="login-error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="connexion.php">
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required class="login-input">
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required class="login-input">
            <button type="submit" class="login-btn">Se connecter</button>
        </form>
        <p class="login-register">Nouveau ? inscrivez-vous <a href="inscription.php">ici</a></p>
    </div>
</section>
<style>
body {
    background: linear-gradient(120deg, #8f94fb 0%, #4e54c8 100%);
    min-height: 100vh;
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.login-section {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
}
.login-card {
    background: #fff;
    padding: 38px 32px 28px 32px;
    border-radius: 18px;
    box-shadow: 0 6px 32px rgba(78,84,200,0.13);
    min-width: 340px;
    max-width: 95vw;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.login-card h2 {
    color: #4e54c8;
    margin-bottom: 18px;
    font-size: 2em;
}
.login-error {
    color: #e74c3c;
    background: #ffeaea;
    border-radius: 8px;
    padding: 8px 16px;
    margin-bottom: 14px;
    font-weight: bold;
    text-align: center;
}
form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
label {
    color: #2d3a4b;
    font-weight: 500;
    margin-bottom: 2px;
}
.login-input {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1em;
    background: #f7f7fa;
    margin-bottom: 6px;
    transition: border 0.2s;
}
.login-input:focus {
    border: 1.5px solid #4e54c8;
    outline: none;
    background: #f0f4ff;
}
.login-btn {
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
}
.login-btn:hover {
    background: linear-gradient(90deg, #8f94fb 0%, #4e54c8 100%);
    box-shadow: 0 4px 16px rgba(78,84,200,0.18);
}
.login-register {
    margin-top: 18px;
    color: #555;
    font-size: 1em;
}
.login-register a {
    color: #4e54c8;
    text-decoration: underline;
    font-weight: bold;
}
@media (max-width: 600px) {
    .login-card { min-width: unset; width: 98vw; padding: 18px 4vw; }
}
</style>