<!DOCTYPE html>
<html lang="sv">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../public/assets/css/main.css">
        <link rel="preload" as="image" href="../public/assets/img/ATM2.jpg">
        <title>My ATM</title>
    </head>

    <body>
        <!--
            overlay och zoom-animation finns kvar precis som förut.
            Skriptet är bytt till atm-overview-ROUTER.js som använder
            router.goTo() istället för window.location.href.
        -->
        <main>
            <div class="page-overlay" id="overlay"></div>

            <div class="image-container">
                <img src="../public/assets/img/Trädpanel.jpg" class="atm-image" id="atm-bg" alt="Bankomater" />

                <a href="index.php?page=real-atm-nr1"                    class="atm-hotspot" style="left:30%;top:35%;width:11.5%;height:43%;" title="Bankomat 1"></a>
                <a href="index.php?page=real-atm-nr1"                        class="atm-hotspot" style="left:45.5%;top:38.5%;width:9.3%;height:37.5%;" title="Bankomat 2"></a>
                <a href="index.php?page=real-atm-nr1"                class="atm-hotspot" style="left:60.4%;top:41.5%;width:6.2%;height:31%;" title="Bankomat 3"></a>
                <a href="index.php?page=around-the-corner-closed-door"   class="atm-hotspot" style="left:70%;top:45%;width:18%;height:54%;" title="Runt-hörnet"></a>
            </div>

            <!--
                OBS: atm-overview-router.js (INTE atm-overview.js).
                router.js ser till att detta skript bara körs en gång.
            -->
            <script src="../public/assets/js/atm-overview-router.js"></script>
        </main>
    </body>
</html>