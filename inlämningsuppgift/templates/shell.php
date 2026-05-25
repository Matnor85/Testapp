<!DOCTYPE html>
<html lang="sv">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="assets/css/main.css">
        <link rel="preload" as="image" href="assets/img/around-corner-door-closed.png">
        <link rel="preload" as="image" href="assets/img/around-corner.png">
        <title>My ATM</title>
        <style>
            #view-container {
                position: relative;
                width: 100%;
                height: 100vh;
            }

            .page-layer {
                position: absolute;
                inset: 0;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.25s ease;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .page-layer.active {
                opacity: 1;
                pointer-events: all;
            }

            .back-button {
                position: fixed;
                top: 10px;
                right: 10px;
                background-color: #007bff;
                color: white;
                padding: 8px 16px;
                text-decoration: none;
                border-radius: 4px;
                z-index: 1000;
                font-family: Arial, sans-serif;
                font-size: 14px;
                cursor: pointer;
                border: none;
            }

            .back-button:hover { background-color: #0056b3; }
            .back-button[hidden] { display: none; }
        </style>
    </head>

    <body>
        <button class="back-button" id="back-btn" hidden onclick="router.back()">Tillbaka</button>

        <div id="view-container"></div>

        <script src="assets/js/router.js"></script>
        <script>
            router.init('home');
        </script>
    </body>
</html>