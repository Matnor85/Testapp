<!doctype html>
<html lang="sv">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="../public/assets/css/main.css" />
        <link rel="preload" as="image" href="../public/assets/img/ATM2.jpg">
        <title>ATM</title>
    </head>
    <body>
        <div class="wrapper">
            <!--
                Tillbaka-knapp tas bort härifrån – shell.php hanterar den.
                <a href="home.php" class="back-button">Tillbaka</a>
            -->

            <main>
                <section class="atm-layout">
                    <div class="real-atm-image">
                        <img src="../public/assets/img/ATM2.jpg" class="real-atm-img" alt="ATM bild" />

                        <!-- Bankomat spegel -->
                        <?php require 'camera.php'; ?>
                        <!-- Bankomat skärm -->
                        <?php require __DIR__ . '/shared/atm_screen.php'; ?>
                        <!-- Kortanimation -->
                        <?php require __DIR__ . '/shared/atm_cardinformation.php'; ?>
                        <!-- Kortläsare -->
                        <?php require __DIR__ . '/shared/atm_card.php'; ?>
                        <!-- Vänster sidoknappar -->
                        <?php require __DIR__ . '/shared/atm_side_buttons.php'; ?>
                        <!-- Sifferknappar -->
                        <?php require __DIR__ . '/shared/atm_num_buttons.php'; ?>
                        <!-- Funktionsknappar -->
                        <?php require __DIR__ . '/shared/atm_fn_buttons.php'; ?>

                        <!--
                            real-atm.js INUTI <main> så att router.js hittar och kör den.
                            Tidigare låg den i <head> med defer – det fungerar inte med
                            fetch-baserad routing eftersom <head> aldrig laddas om.
                        -->
                        <script src="../public/assets/js/real-atm.js"></script>

                        <!--
                            camera.js likaså – om camera.php inte laddar den själv.
                        -->
                        <script src="../public/assets/js/camera.js"></script>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>