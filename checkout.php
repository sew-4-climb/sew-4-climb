<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: login_google.php');
    exit;
}

$email = $_SESSION['user_email'];
$nome  = $_SESSION['user_name'] ?? 'Cliente';
$cart  = $_SESSION['cart'] ?? [];

$messaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conferma_ordine'])) {
    if (!empty($cart)) {
        $to = $email;
        $subject = "Conferma Ordine Sew 4 Climb — Il tuo Sacchetto Custom!";
        $body = "Ciao $nome,\n\nGrazie per il tuo ordine su Sew 4 Climb!\nAbbiamo preso in carico la tua richiesta e i nostri artigiani inizieranno a lavorare la tua Chalk Bag personalizzata.\n\nI dettagli dell'ordine:\n";
        
        foreach ($cart as $index => $item) {
            $body .= "\n- " . $item['nome'] . " (€" . number_format($item['prezzo'], 2) . ")\n";
            if (!empty($item['dettagli'])) {
                foreach ($item['dettagli'] as $k => $v) {
                    $body .= "   * $k: $v\n";
                }
            }
        }
        
        $body .= "\nSpediremo il pacco non appena le cuciture saranno completate.\n\nGrazie da Sew 4 Climb!";
        $headers = "From: ordini@sew4climb.local\r\nReply-To: info@sew4climb.local";

        @mail($to, $subject, $body, $headers);

        unset($_SESSION['cart']);
        $messaggio = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Checkout & Conferma — Sew 4 Climb</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f1ea; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 650px; margin: 0 auto; background: white; padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        h1 { color: #1a1a1a; margin-top: 0; font-size: 1.8rem; }
        .user-badge { background: #e8f5e9; border: 1px solid #2c5e3b; padding: 14px 18px; border-radius: 12px; color: #2c5e3b; font-weight: bold; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .summary-card { background: #fafafa; border: 1px solid #eee; padding: 20px; border-radius: 12px; margin-bottom: 25px; }
        .btn-pay { background: #2c5e3b; color: white; border: none; padding: 18px; font-size: 1.1rem; font-weight: bold; border-radius: 30px; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-pay:hover { background: #21472c; }
        .alert-success { background: #e8f5e9; color: #2e7d32; padding: 25px; border-radius: 12px; text-align: center; border: 1px solid #2e7d32; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container">
    <?php if ($messaggio === 'success'): ?>
        <div class="alert-success">
            <h2 style="margin-top:0;">✓ Ordine Confermato con Successo!</h2>
            <p>Abbiamo inviato un'email di conferma all'indirizzo <strong><?= htmlspecialchars($email) ?></strong>.</p>
            <p>I nostri artigiani si metteranno subito all'opera nel laboratorio Sew 4 Climb!</p>
            <a href="index.php" style="display:inline-block; margin-top:15px; background:#2c5e3b; color:white; padding:12px 25px; border-radius:25px; text-decoration:none; font-weight:bold;">Torna alla Home Page</a>
        </div>
    <?php else: ?>
        <h1>Riepilogo Ordine & Checkout</h1>
        
        <div class="user-badge">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Cliente Autenticato: <?= htmlspecialchars($nome) ?> (<?= htmlspecialchars($email) ?>)</span>
        </div>

        <div class="summary-card">
            <h3 style="margin-top:0;">Articoli nel tuo ordine:</h3>
            <?php foreach ($cart as $item): ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                    <strong><?= htmlspecialchars($item['nome']) ?></strong>
                    <span>€<?= number_format($item['prezzo'], 2, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST" action="checkout.php">
            <input type="hidden" name="conferma_ordine" value="1">
            <button type="submit" class="btn-pay">Conferma l'Ordine & Invia Email</button>
        </form>

        <a href="cart.php" class="back-link">&larr; Torna al carrello</a>
    <?php endif; ?>
</div>

</body>
</html>