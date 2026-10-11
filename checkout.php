<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/mail_config.php'; // Includi il file mail

if (!isset($_SESSION['user_email'])) {
    header('Location: login_google.php?from=checkout');
    exit;
}

$email = $_SESSION['user_email'];
$nome  = $_SESSION['user_name'] ?? 'Cliente';
$cart  = $_SESSION['cart'] ?? [];

$messaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conferma_ordine'])) {
    if (!empty($cart)) {
        
        // 1. SCALAMENTO QUANTITÀ DAL DATABASE MYSQL
        if (isset($pdo) && $pdo !== null) {
            foreach ($cart as $item) {
                if (!empty($item['dettagli'])) {
                    foreach ($item['dettagli'] as $part => $fabricName) {
                        if ($fabricName !== 'Nessuno' && !empty($fabricName)) {
                            try {
                                $stmt = $pdo->prepare("UPDATE stoffe SET quantita = GREATEST(0, quantita - 1) WHERE LOWER(TRIM(nome)) = LOWER(TRIM(?)) LIMIT 1");
                                $stmt->execute([$fabricName]);
                            } catch (Exception $e) {
                                // Log errore scalamento DB
                            }
                        }
                    }
                }
            }
        }

        // 2. COSTRUZIONE EMAIL HTML PER CLIENTE E LABORATORIO SARTORIA
        $subject = "Conferma Ordine Sew 4 Climb — Chalk Bag Custom!";
        
        $dettagliHtml = "";
        $totaleOrdine = 0;

        foreach ($cart as $index => $item) {
            $totaleOrdine += $item['prezzo'];
            $dettagliHtml .= "<div style='background:#f9f9f9; padding:15px; border-radius:10px; margin-bottom:15px;'>";
            $dettagliHtml .= "<h3 style='margin:0 0 10px 0; color:#2c5e3b;'>" . htmlspecialchars($item['nome']) . " (€" . number_format($item['prezzo'], 2) . ")</h3>";
            $dettagliHtml .= "<ul style='margin:0; padding-left:20px; font-size:14px; color:#555;'>";
            
            if (!empty($item['dettagli'])) {
                foreach ($item['dettagli'] as $k => $v) {
                    $dettagliHtml .= "<li><strong>" . ucfirst($k) . ":</strong> " . htmlspecialchars($v) . "</li>";
                }
            }
            $dettagliHtml .= "</ul></div>";
        }

        $htmlBody = "
        <div style='font-family: Arial, sans-serif; background:#f4f1ea; padding:30px;'>
            <div style='max-width:600px; margin:0 auto; background:white; padding:30px; border-radius:16px;'>
                <h1 style='color:#2c5e3b; margin-top:0;'>SEW 4 CLIMB</h1>
                <h2>Grazie per il tuo ordine, $nome!</h2>
                <p>Abbiamo preso in carico la tua richiesta. I nostri artigiani inizieranno a lavorare subito alla tua Chalk Bag personalizzata.</p>
                <hr style='border:none; border-top:1px solid #eee; margin:20px 0;'>
                <h3>Riepilogo del tuo Sacchetto Custom:</h3>
                $dettagliHtml
                <h3 style='text-align:right; color:#2c5e3b;'>Totale Ordine: €" . number_format($totaleOrdine, 2, ',', '.') . "</h3>
                <hr style='border:none; border-top:1px solid #eee; margin:20px 0;'>
                <p style='font-size:12px; color:#888;'>Sew 4 Climb — Upcycled Climbing Gear</p>
            </div>
        </div>
        ";

        // 3. INVIO EMAIL CON SMTP GMAIL
        inviaEmailOrdine($email, $nome, $subject, $htmlBody);

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
        :root { --primary: #2c5e3b; --primary-dark: #21472c; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f1ea; margin: 0; padding: 40px 20px; color: #333; }
        .container { max-width: 650px; margin: 0 auto; background: white; padding: 35px; border-radius: 20px; box-shadow: 0 4px 25px rgba(0,0,0,0.06); }
        h1 { color: #1a1a1a; margin-top: 0; font-size: 1.8rem; }
        .user-badge { background: rgba(44, 94, 59, 0.08); border: 1px solid rgba(44, 94, 59, 0.25); padding: 14px 18px; border-radius: 12px; color: var(--primary); font-weight: bold; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
        .summary-card { background: #fafafa; border: 1px solid #eee; padding: 20px; border-radius: 14px; margin-bottom: 25px; }
        .btn-pay { background: var(--primary); color: white; border: none; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 35px; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; transition: background 0.2s; }
        .btn-pay:hover { background: var(--primary-dark); }
        .alert-success { background: #e8f5e9; color: #2e7d32; padding: 30px; border-radius: 16px; text-align: center; border: 1px solid #2e7d32; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #666; text-decoration: none; font-size: 0.9rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <?php if ($messaggio === 'success'): ?>
        <div class="alert-success">
            <h2 style="margin-top:0;">✓ Ordine Confermato con Successo!</h2>
            <p>Abbiamo inviato l'email di conferma all'indirizzo <strong><?= htmlspecialchars($email) ?></strong> e la scheda tecnica al nostro laboratorio sartoriale.</p>
            <p>I nostri artigiani si metteranno subito all'opera!</p>
            <a href="configuratore.php" style="display:inline-block; margin-top:15px; background:var(--primary); color:white; padding:14px 28px; border-radius:30px; text-decoration:none; font-weight:bold;">Crea un altro sacchetto &rarr;</a>
        </div>
    <?php else: ?>
        <h1>Riepilogo Ordine & Checkout</h1>
        
        <div class="user-badge">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Cliente Autenticato: <?= htmlspecialchars($nome) ?> (<?= htmlspecialchars($email) ?>)</span>
        </div>

        <div class="summary-card">
            <h3 style="margin-top:0;">Articoli nel tuo ordine:</h3>
            <?php 
            $totale = 0;
            foreach ($cart as $item): 
                $totale += $item['prezzo'];
            ?>
                <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:1.05rem;">
                    <strong><?= htmlspecialchars($item['nome']) ?></strong>
                    <span style="font-weight:bold; color:var(--primary);">€<?= number_format($item['prezzo'], 2, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>
            
            <hr style="border:none; border-top:1px solid #eee; margin:15px 0;">
            <div style="display:flex; justify-content:space-between; font-weight:800; font-size:1.2rem;">
                <span>Totale Da Pagare:</span>
                <span style="color:var(--primary);">€<?= number_format($totale, 2, ',', '.') ?></span>
            </div>
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