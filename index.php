<?php 
include_once('header.php');
?>

<section class="home-section" style="display:flex;flex-wrap:wrap;gap:38px;justify-content:center;align-items:stretch;padding:32px 0;">
    <div class="home-card" style="background:#fff;border-radius:18px;box-shadow:0 4px 24px #4e54c820;padding:32px 28px;max-width:420px;width:95vw;margin-bottom:18px;display:flex;flex-direction:column;justify-content:center;align-items:center;">
        <h3 style="color:#4e54c8;font-size:1.5em;font-weight:700;margin-bottom:12px;text-align:center;">Le Gma'h Hessed LéYaacov</h3>
        <p style="font-size:1.08em;color:#222;margin-bottom:10px;text-align:center;">Ce site a été créé pour répondre aux besoins des fidèles de la Kehila</p>
        <p style="color:#555;font-size:1em;line-height:1.5;text-align:center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec porttitor enim ante, ut aliquam velit porta eget. Suspendisse iaculis elit vitae ligula faucibus pulvinar. Maecenas sem libero, viverra a bibendum a, mollis a nunc. Vivamus quis accumsan arcu. Quisque quis velit sagittis, tincidunt lectus sed, porttitor lorem. Nulla commodo eu eros semper accumsan. Quisque dolor velit, euismod quis velit vel, dapibus gravida nulla. Maecenas congue convallis enim et lacinia. Aenean dapibus quam risus, et sollicitudin tellus efficitur cursus. Aliquam eu laoreet augue. Cras luctus tempus massa, vitae condimentum dolor tristique vel. Donec in laoreet tortor.</p>
    </div>
    <div class="home-card" style="background:#fff;border-radius:18px;box-shadow:0 4px 24px #4e54c820;padding:32px 28px;max-width:420px;width:95vw;margin-bottom:18px;display:flex;flex-direction:column;justify-content:center;align-items:center;">
        <h3 style="color:#4e54c8;font-size:1.5em;font-weight:700;margin-bottom:12px;text-align:center;">Comment ça marche ?</h3>
        <p style="color:#555;font-size:1em;line-height:1.5;text-align:center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec porttitor enim ante, ut aliquam velit porta eget. Suspendisse iaculis elit vitae ligula faucibus pulvinar. Maecenas sem libero, viverra a bibendum a, mollis a nunc. Vivamus quis accumsan arcu. Quisque quis velit sagittis, tincidunt lectus sed, porttitor lorem. Nulla commodo eu eros semper accumsan. Quisque dolor velit, euismod quis velit vel, dapibus gravida nulla. Maecenas congue convallis enim et lacinia. Aenean dapibus quam risus, et sollicitudin tellus efficitur cursus. Aliquam eu laoreet augue. Cras luctus tempus massa, vitae condimentum dolor tristique vel. Donec in laoreet tortor.</p>
    </div>
</section>
<style>
@media (max-width: 700px) {
    .home-section {
        flex-direction:column;
        gap:18px;
        padding:12px 0;
    }
    .home-card {
        padding: 18px 6vw;
        font-size: 1em;
        margin-bottom: 10px;
    }
    .home-card h3 {
        font-size: 1.1em;
    }
}
</style>
<?php
include_once('footer.php');
?>
