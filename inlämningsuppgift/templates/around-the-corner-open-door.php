<!DOCTYPE html>
<html lang="sv">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/css/main.css">
        <title>My ATM – Öppen dörr</title>
    </head>

    <body>
        <main>
            <div class="image-container">
                <img
                    src="assets/img/around-corner.png"
                    class="atm-image"
                    id="atm-bg"
                    alt="Bankomat med öppen dörr"
                />

                <canvas id="matrix" class="matrix-canvas"></canvas>

                <div id="server-overlay" class="atm-hotspot"></div>

                <a href="index.php?page=around-the-corner-closed-door" class="atm-hotspot" style="left:5.3%;top:42.2%;width:2%;height:6%;" title="Stäng dörren"></a>
                <a href="index.php?page=admin_login"                                  class="atm-hotspot" style="left:13.5%;top:43.6%;width:3.9%;height:6%;" title="Admin-dator"></a>

                <script src="assets/js/matrix.js"></script>
            </div>
        </main>
    </body>
</html>