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
<main class="login-main" style="background:linear-gradient(120deg,#f7f8fa 0%,#e9eafc 100%);min-height:100vh;display:flex;justify-content:center;align-items:center;">
    <section class="login-section" style="width:100vw;display:flex;justify-content:center;align-items:center;min-height:80vh;">
        <div class="login-card" style="background:rgba(255,255,255,0.98);padding:32px 6vw 28px 6vw;border-radius:18px;box-shadow:0 8px 32px #4e54c81a;max-width:420px;width:100%;display:flex;flex-direction:column;align-items:center;animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);">
            <h2 style="color:#222;margin-bottom:18px;font-size:2em;font-weight:700;font-family:'Segoe UI','Roboto',Arial,sans-serif;letter-spacing:1px;">Connexion</h2>
            <?php if (isset($error)): ?>
                <p class="login-error" style="color:#e74c3c;background:#ffeaea;border-radius:8px;padding:8px 16px;margin-bottom:14px;font-weight:bold;text-align:center;animation:fadeInCard 0.7s;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" action="connexion.php" style="width:100%;display:flex;flex-direction:column;gap:16px;">
                <label for="email" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Email :</label>
                <input type="email" name="email" id="email" required class="login-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
                <label for="password" style="color:#2d3a4b;font-weight:500;margin-bottom:2px;">Mot de passe :</label>
                <input type="password" name="password" id="password" required class="login-input" style="padding:13px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:1.08em;background:#f7f7fa;margin-bottom:6px;transition:border 0.2s;width:100%;box-sizing:border-box;">
                <button type="submit" class="login-btn" style="background:linear-gradient(90deg,#e9eafc 0%,#4e54c8 100%);color:#222;border:none;border-radius:10px;padding:14px 0;font-size:1.13em;font-weight:bold;margin-top:10px;cursor:pointer;box-shadow:0 2px 8px #e9eafc;transition:all 0.25s cubic-bezier(.4,2,.6,1);width:100%;">Se connecter</button>
            </form>
            <p class="login-register" style="margin-top:18px;color:#555;font-size:1em;text-align:center;">Nouveau ? inscrivez-vous <a href="inscription.php" style="color:#4e54c8;text-decoration:underline;font-weight:bold;">ici</a></p>
        </div>
    </section>
</main>
<style>
@keyframes fadeInCard {
    0% { opacity:0; transform:translateY(40px) scale(0.98); }
    100% { opacity:1; transform:translateY(0) scale(1); }
}
.login-card {
    backdrop-filter:blur(2px);
}
.login-btn:hover, .login-btn:focus {
    background:linear-gradient(90deg,#4e54c8 0%,#8f94fb 100%);
    color:#fff;
    box-shadow:0 8px 32px #4e54c830;
    transform:scale(1.04);
    border:1px solid #4e54c8;
}
@media (max-width: 700px) {
    .login-card {
        padding:18px 2vw;
        font-size:1em;
        min-width:unset;
        width:100vw;
        border-radius:0;
        box-shadow:none;
    }
    .login-card h2 {
        font-size:1.3em;
    }
    .login-input {
        font-size:1em;
        padding:10px 8px;
    }
    .login-btn {
        font-size:1em;
        padding:12px 0;
    }
}
</style>