<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sew 4 Climb — Chalk Bag Uniche e Sostenibili</title>
    <style>
        :root {
            --primary: #2c5e3b;
            --primary-dark: #1e4228;
            --bg-cream: #f4f1ea;
            --text-dark: #1a1a1a;
            --text-muted: #555555;
        }

        * { box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-cream);
            color: var(--text-dark);
            overflow-x: hidden;
            line-height: 1.6;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 40px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.4rem;
            font-weight: 900;
            letter-spacing: 2px;
            color: var(--text-dark);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .cart-icon {
            position: relative;
            display: flex;
            align-items: center;
            color: var(--text-dark);
            text-decoration: none;
            margin-right: 5px;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #e53935;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .btn-nav {
            background-color: var(--primary);
            color: white !important;
            padding: 10px 22px;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(44, 94, 59, 0.25);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-nav:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .alert-toast {
            background: #2e7d32;
            color: white;
            text-align: center;
            padding: 14px;
            font-weight: bold;
            font-size: 0.95rem;
            position: relative;
            z-index: 1001;
        }

        .hero-parallax {
            position: relative;
            height: 85vh;
            background-image: linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.55)), url('https://images.unsplash.com/photo-1522163182402-834f871fd851?auto=format&fit=crop&w=1600&q=80');
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }

        .hero-content {
            max-width: 850px;
            animation: fadeIn 1.2s ease-in-out;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 900;
            letter-spacing: -1px;
            line-height: 1.15;
            margin-bottom: 20px;
            text-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 35px;
            opacity: 0.95;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
            font-weight: 300;
        }

        .scroll-section {
            padding: 100px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .split-block {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 100px;
        }

        .split-block.reverse { direction: rtl; }
        .split-block.reverse .block-text { direction: ltr; }

        .block-text h2 {
            font-size: 2.3rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .block-text p {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .badge-tag {
            display: inline-block;
            background: #e2dacd;
            color: #554838;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .img-frame {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
            transition: transform 0.4s ease;
        }

        .img-frame:hover { transform: scale(1.02); }

        .img-frame img {
            width: 100%;
            height: 480px;
            object-fit: cover;
            display: block;
        }

        .banner-parallax {
            position: relative;
            padding: 120px 20px;
            background-image: linear-gradient(rgba(30, 66, 40, 0.75), rgba(44, 94, 59, 0.85)), url('img/ragazzo-lavoro.jpg');
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            color: white;
            text-align: center;
            margin: 60px 0;
        }

        .banner-parallax-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .banner-parallax-content h2 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .banner-parallax-content p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 30px;
        }

        .grid-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
        }

        .card-item {
            background: white;
            padding: 40px 30px;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }

        .card-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .card-icon {
            margin-bottom: 20px;
            color: var(--primary);
        }

        .card-item h3 {
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        .card-item p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin: 0;
        }

        .cta-section {
            background: #111;
            color: white;
            text-align: center;
            padding: 120px 20px;
            position: relative;
        }

        .cta-container {
            max-width: 850px;
            margin: 0 auto;
        }

        .cta-container h2 {
            font-size: 3.2rem;
            font-weight: 900;
            margin-bottom: 25px;
            letter-spacing: -1px;
        }

        .cta-container p {
            font-size: 1.2rem;
            color: #aaa;
            margin-bottom: 45px;
        }

        .btn-cta-giant {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background-color: var(--primary);
            color: white;
            font-size: 1.25rem;
            font-weight: 800;
            padding: 22px 50px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 12px 30px rgba(44, 94, 59, 0.4);
            transition: all 0.3s ease;
        }

        .btn-cta-giant:hover {
            background-color: #38784c;
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 18px 40px rgba(44, 94, 59, 0.5);
        }

        .footer {
            background: #0a0a0a;
            color: #666;
            text-align: center;
            padding: 30px 20px;
            font-size: 0.85rem;
            border-top: 1px solid #1f1f1f;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 900px) {
            .split-block, .split-block.reverse { grid-template-columns: 1fr; direction: ltr; }
            .grid-cards { grid-template-columns: 1fr; }
            .hero-content h1 { font-size: 2.4rem; }
            .hero-parallax, .banner-parallax { background-attachment: scroll; }
        }
    </style>
</head>
<body>

<?php if (isset($_GET['added'])): ?>
    <div class="alert-toast">✓ Il tuo sacchetto custom è stato aggiunto al carrello! Clicca sull'icona in alto a destra per vederlo.</div>
<?php endif; ?>

<!-- NAVBAR -->
<div class="header">
    <a href="index.php" class="logo">SEW 4 CLIMB</a>
    <div class="nav-links">
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
            <a href="admin.php" style="color:var(--primary); font-weight:bold;">Magazzino Admin</a>
        <?php endif; ?>

        <a href="cart.php" class="cart-icon" aria-label="Carrello">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <?php if (!empty($_SESSION['cart'])): ?>
                <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
            <?php endif; ?>
        </a>

        <?php if (isset($_SESSION['user_email'])): ?>
            <a href="reset.php" style="color:#888;">Logout</a>
        <?php else: ?>
            <a href="login_google.php">Accedi</a>
        <?php endif; ?>
        
        <a href="configuratore.php" class="btn-nav">
            <span>Crea il tuo Sacchetto</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
</div>

<!-- HERO -->
<div class="hero-parallax">
    <div class="hero-content">
        <h1>Ogni tessuto ha una storia.<br>Tu crei il suo futuro.</h1>
        <p>Chalk bag uniche da arrampicata nate dal recupero di denim vintage, eccedenze sartoriali e tessuti unici. Disegna il tuo pezzo irripetibile con Sew 4 Climb.</p>
        <a href="configuratore.php" class="btn-cta-giant">
            <span>Inizia la Configurazione Custom</span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
</div>

<div class="scroll-section">
    <!-- BLOCCO 1 -->
    <div class="split-block">
        <div class="block-text">
            <span class="badge-tag">Recupero Circolare</span>
            <h2>Dagli Scarti di Negozio alle Pareti di Arrampicata.</h2>
            <p>Non utilizziamo tessuti di nuova produzione. Recuperiamo rimanenze di magazzino, stoffe dimenticate nei retrobottega dei negozi e scarti di taglio che altrimenti andrebbero sprecati.</p>
            <p>Ogni trama, cucitura e colore è selezionato a mano nel nostro laboratorio per dare vita a prodotti resistenti e sostenibili.</p>
        </div>
        <div class="img-frame">
            <img src="https://images.unsplash.com/photo-1578932750294-f5075e85f44a?auto=format&fit=crop&w=1000&q=80" alt="Recupero Stoffe e Denim Vintage">
        </div>
    </div>

    <!-- BLOCCO 2 -->
    <div class="split-block reverse">
        <div class="block-text">
            <span class="badge-tag">Sartoria Artigianale</span>
            <h2>Fatto a Mano Pezzo dopo Pezzo.</h2>
            <p>I nostri sacchetti vengono assemblati e cuciti a mano nel laboratorio artigianale, unendo parti di vera stoffa vintage e jeans di recupero con massima cura dei dettagli.</p>
            <p>Dalle cuciture rinforzate all'inserimento delle cerniere e delle etichette in pelle, ogni passaggio garantisce resistenza e stile inconfondibile.</p>
        </div>
        <div class="img-frame">
            <img src="img/tavolo-cucito.jpg" alt="Lavorazione Artigianale Fatta a Mano e Sartoria">
        </div>
    </div>
</div>

<!-- BANNER INTERMEDIO -->
<div class="banner-parallax">
    <div class="banner-parallax-content">
        <h2>Un Prodotto Artigianale, 100% Tuo.</h2>
        <p>Scegli la parte superiore, il fondo, i lati etnici, il colore della cerniera zip e aggiungi gli accessori che desideri.</p>
    </div>
</div>

<!-- CARDS DEI VALORI -->
<div class="scroll-section">
    <div style="text-align:center; max-width:700px; margin:0 auto 50px auto;">
        <h2 style="font-size:2.5rem; font-weight:800; margin-bottom:15px;">Perché scegliere Sew 4 Climb</h2>
        <p style="color:var(--text-muted); font-size:1.1rem;">L'unione perfetta tra passione per l'arrampicata e rispetto per l'ambiente.</p>
    </div>

    <div class="grid-cards">
        <div class="card-item">
            <div class="card-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            </div>
            <h3>Zero Spreco</h3>
            <p>Riduciamo l'impatto ambientale trasformando scarti tessili in attrezzatura da bouldering di altissima qualità.</p>
        </div>

        <div class="card-item">
            <div class="card-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h3>Fatto a Mano</h3>
            <p>Ogni chalk bag è assemblata e cucita artigianalmente garantendo massimo rinforzo sulle cuciture sottoposte a sforzo.</p>
        </div>

        <div class="card-item">
            <div class="card-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <h3>Pezzo Unico</h3>
            <p>Grazie alla combinazione delle varie stoffe del magazzino, nessun altro climber sulla parete avrà un sacchetto uguale al tuo.</p>
        </div>
    </div>
</div>

<!-- CALL TO ACTION FINALE -->
<div class="cta-section">
    <div class="cta-container">
        <h2>Pronto a creare la tua Chalk Bag?</h2>
        <p>Entra nel configuratore interattivo, seleziona i tessuti presenti nel magazzino e personalizza il tuo sacchetto passo dopo passo.</p>
        <a href="configuratore.php" class="btn-cta-giant">
            <span>Configura Ora il Tuo Sacchetto</span>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
</div>

<div class="footer">
    &copy; 2026 Sew 4 Climb — Sacchetti per arrampicata creati con materiali riciclati e denim di recupero.
</div>

</body>
</html>