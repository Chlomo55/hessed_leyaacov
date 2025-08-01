<?php 
include_once('header.php');
?>
<main class="contact-main" style="display:flex;justify-content:center;align-items:center;min-height:60vh;background:linear-gradient(120deg,#f7f8fa 0%,#e9eafc 100%);">
    <div class="contact-card" style="background:rgba(255,255,255,0.98);border-radius:18px;box-shadow:0 8px 32px #4e54c81a;padding:44px 38px;max-width:420px;width:95vw;text-align:center;animation:fadeInCard 1.1s cubic-bezier(.4,2,.6,1);">
        <h2 style="color:#222;font-size:2.1em;font-weight:700;margin-bottom:18px;font-family:'Segoe UI', 'Roboto', Arial, sans-serif;letter-spacing:1px;">Contactez-nous</h2>
        <p style="font-size:1.18em;color:#444;margin-bottom:22px;line-height:1.6;">Vous pouvez nous contacter par email à l'adresse suivante :</p>
        <a href="mailto:chlomo.freoua@gmail.com" class="contact-btn" style="display:inline-block;background:linear-gradient(90deg,#e9eafc 0%,#4e54c8 100%);color:#222;padding:13px 32px;border-radius:10px;text-decoration:none;font-weight:600;font-size:1.13em;box-shadow:0 2px 12px #4e54c820;transition:all 0.25s cubic-bezier(.4,2,.6,1);border:1px solid #e9eafc;">chlomo.freoua@gmail.com</a>
        <div style="margin-top:32px;color:#888;font-size:1em;">Nous vous répondrons dans les plus brefs délais.</div>
    </div>
</main>
<style>
@keyframes fadeInCard {
    0% { opacity:0; transform:translateY(40px) scale(0.98); }
    100% { opacity:1; transform:translateY(0) scale(1); }
}
.contact-btn:hover, .contact-btn:focus {
    background:linear-gradient(90deg,#4e54c8 0%,#8f94fb 100%);
    color:#fff;
    box-shadow:0 8px 32px #4e54c830;
    transform:scale(1.04);
    border:1px solid #4e54c8;
}
.contact-card {
    backdrop-filter:blur(2px);
}
@media (max-width: 600px) {
    .contact-card {
        padding: 18px 6vw;
        font-size: 1em;
    }
    .contact-main {
        min-height: 40vh;
        padding: 0;
    }
    .contact-card h2 {
        font-size: 1.3em;
    }
    .contact-btn {
        font-size: 1em;
        padding: 10px 12px;
    }
}
</style>