<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    unset($_SESSION['cart']);
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Il Tuo Carrello — Sew 4 Climb</title>
    <style>
        :root {
            --primary: #2c5e3b;
            --primary-dark: #21472c;
            --bg-cream: #f4f1ea;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg-cream); margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 35px; border-radius: 20px; box-shadow: 0 4px 25px rgba(0,0,0,0.06); }
        h1 { margin-top: 0; font-size: 1.8rem; display: flex; align-items: center; gap: 12px; color: #1a1a1a; }
        .cart-item { display: flex; justify-content: space-between; background: #fafafa; border: 1px solid #eee; padding: 22px; border-radius: 16px; margin-bottom: 20px; }
        .item-title { font-weight: 800; font-size: 1.15rem; color: #1a1a1a; }
        .item-price { font-weight: 800; color: var(--primary); font-size: 1.3rem; }
        .details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px 20px; margin-top: 12px; font-size: 0.88rem; color: #555; background: white; padding: 12px 16px; border-radius: 10px; border: 1px solid #eaeaea; }
        .btn-checkout { background: var(--primary); color: white; border: none; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 35px; cursor: pointer; width: 100%; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; box-sizing: border-box; transition: background 0.2s; }
        .btn-checkout:hover { background: var(--primary-dark); }
        .empty-cart { text-align: center; padding: 50px 20px; color: #888; }
        .back-link { display: flex; align-items: center; justify-content: center; gap: 8px; text-align: center; margin-top: 22px; color: #666; text-decoration: none; font-size: 0.9rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <h1>
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        Il Tuo Carrello Sew 4 Climb
    </h1>

    <?php if (empty($cart)): ?>
        <div class="empty-cart">
            <p style="font-size:1.1rem;">Il carrello è attualmente vuoto.</p>
            <a href="configuratore.php" style="color:var(--primary); font-weight:bold; text-decoration:none;">Crea subito il tuo primo sacchetto custom &rarr;</a>
        </div>
    <?php else: ?>
        <?php foreach ($cart as $item): ?>
            <div class="cart-item">
                <div style="flex-grow: 1; margin-right: 20px;">
                    <div class="item-title"><?= htmlspecialchars($item['nome']) ?></div>
                    
                    <?php if (!empty($item['dettagli'])): ?>
                        <div class="details-grid">
                            <?php 
                            $labels = [
                                'top_front' => 'Sup. Fronte', 'top_back' => 'Sup. Retro',
                                'bottom_front' => 'Fondo Fronte', 'bottom_back' => 'Fondo Retro',
                                'bottom_base' => 'Base Fondo', 'side_left' => 'Fianco SX',
                                'side_right' => 'Fianco DX', 'pocket' => 'Taschina',
                                'brush' => 'Spazzolina', 'tag' => 'Etichetta'
                            ];
                            foreach ($item['dettagli'] as $part => $fabric): 
                                $label = $labels[$part] ?? ucfirst($part);
                            ?>
                                <div><strong><?= $label ?>:</strong> <?= htmlspecialchars($fabric) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="item-price">€<?= number_format($item['prezzo'], 2, ',', '.') ?></div>
            </div>
        <?php endforeach; ?>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <a href="cart.php?action=clear" style="color:#c62828; font-size:0.88rem; font-weight:bold; text-decoration:none;">Svuota Carrello</a>
            <a href="configuratore.php" style="color:var(--primary); font-size:0.9rem; font-weight:bold; text-decoration:none;">+ Aggiungi un altro sacchetto</a>
        </div>

        <a href="checkout.php" class="btn-checkout">
            <span>Procedi all'Ordine</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    <?php endif; ?>

    <a href="configuratore.php" class="back-link">&larr; Torna al Configuratore</a>
</div>

</body>
</html>