<?php
// Attivazione temporanea diagnostica errori PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurazione o inclusioni eventuali
// require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sew 4 Climb - Prodotto Artigianale Fatto a Mano</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f9f8f6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Banner Artigianale */
        .banner-artigianale {
            position: relative;
            background: linear-gradient(rgba(26, 60, 43, 0.85), rgba(26, 60, 43, 0.85)), url('img/ragazzo-lavoro.jpg') center/cover no-repeat;
            color: #ffffff;
            text-align: center;
            padding: 100px 20px;
            margin-bottom: 60px;
            border-radius: 8px;
        }
        .banner-artigianale h1 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .banner-artigianale p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Sezione Fatto a Mano */
        .section-fatto-a-mano {
            display: flex;
            align-items: center;
            gap: 40px;
            margin-bottom: 80px;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .section-fatto-a-mano .text-content {
            flex: 1;
        }
        .section-fatto-a-mano h2 {
            font-size: 2.2rem;
            color: #1a3c2b;
            margin-bottom: 20px;
        }
        .section-fatto-a-mano p {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #555;
        }
        .section-fatto-a-mano .image-container {
            flex: 1;
            text-align: center;
        }
        .section-fatto-a-mano img {
            width: 100%;
            max-width: 500px;
            height: auto;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .section-fatto-a-mano {
                flex-direction: column;
                padding: 20px;
            }
            .banner-artigianale h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

    <?php if (file_exists(__DIR__ . '/includes/header.php')) include __DIR__ . '/includes/header.php'; ?>

    <!-- Banner Prodotto Artigianale -->
    <section class="banner-artigianale">
        <div class="container">
            <h1>Un Prodotto Artigianale, 100% Tuo.</h1>
            <p>Scegli la parte superiore, il fondo, i lati etnici, il colore della cerniera zip e aggiungi gli accessori che desideri.</p>
        </div>
    </section>

    <div class="container">
        <!-- Sezione Fatto a mano, pezzo dopo pezzo -->
        <section class="section-fatto-a-mano">
            <div class="text-content">
                <h2>Fatto a mano, pezzo dopo pezzo</h2>
                <p>Ogni sacchetto e accessorio Sew 4 Climb prende vita nel nostro laboratorio artigianale. Dalla selezione e dal taglio dei tessuti recuperati fino alla cucitura finale, curiamo ogni singolo dettaglio per garantirti un pezzo unico, resistente e sostenibile.</p>
            </div>
            <div class="image-container">
                <img src="img/tavolo-cucito.jpg" alt="Fatto a mano pezzo dopo pezzo - Tavolo da lavoro e macchina da cucire">
            </div>
        </section>
    </div>

    <?php if (file_exists(__DIR__ . '/includes/footer.php')) include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>