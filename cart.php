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
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f1ea; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        h1 { margin-top: 0; font-size: 1.8rem; display: flex; align-items: center; gap: 12px; }
        .cart-item { display: flex; justify-content: space-between; background: #fafafa; border: 1px solid #eee; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
        .item-title { font-weight: bold; font-size: 1.1rem; }
        .item-price { font-weight: bold; color: #2c5e3b; font-size: 1.2rem; }
        .details-list { font-size: 0.85rem; color: #666; margin-top: 10px; padding-left: 20px; }
        .btn-checkout { background: #2c5e3b; color: white; border: none; padding: 16px; font-size: 1.1rem; font-weight: bold; border-radius: 30px; cursor: pointer; width: 100%; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; box-sizing: border-box; }
        .btn-checkout:hover { background: #21472c; }
        .empty-cart { text-align: center; padding: 40px; color: #888; }
        .back-link { display: flex; align-items: center; justify-content: center; gap: 8px; text-align: center; margin-top: 20px; color: #666; text-decoration: none; font-size: 0.9rem; }
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
            <p>Il carrello è vuoto.</p>
            <a href="configuratore.php" style="color:#2c5e3b; font-weight:bold; text-decoration:none;">Crea subito il tuo primo sacchetto &rarr;</a>
        </div>
    <?php else: ?>
        <?php foreach ($cart as $item): ?>
            <div class="cart-item">
                <div>
                    <div class="item-title"><?= htmlspecialchars($item['nome']) ?></div>
                    <ul class="details-list">
                        <?php if (!empty($item['dettagli'])): ?>
                            <?php foreach ($item['dettagli'] as $part => $fabric): ?>
                                <li><strong><?= ucfirst($part) ?>:</strong> <?= htmlspecialchars($fabric) ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Configurazione custom selezionata</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="item-price">€<?= number_format($item['prezzo'], 2, ',', '.') ?></div>
            </div>
        <?php endforeach; ?>

        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <a href="cart.php?action=clear" style="color:#c62828; font-size:0.85rem; text-decoration:none;">Svuota Carrello</a>
            <a href="configuratore.php" style="color:#2c5e3b; font-size:0.85rem; font-weight:bold; text-decoration:none;">+ Aggiungi un altro sacchetto</a>
        </div>

        <a href="checkout.php" class="btn-checkout">
            <span>Procedi all'Ordine</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    <?php endif; ?>

    <a href="index.php" class="back-link">&larr; Torna alla Home Page</a>
</div>

</body>
</html>