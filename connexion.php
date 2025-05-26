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
<section>
    <div>
        <h2>Connexion</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="connexion.php">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <br>
            <label for="password">Mot de passe:</label>
            <input type="password" name="password" id="password" required>
            <br>
            <button type="submit">Se connecter</button>
        </form>
        <p>Nouveau ? inscrivez-vous <a href="inscription.php">ici</a></p>
    </div>
</section>