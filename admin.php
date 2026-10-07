<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/google_config.php';
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
    echo "<h2>Accesso Negato</h2>";
    echo "<p>Non hai i permessi per accedere al magazzino admin.</p>";
    echo "<a href='login_google.php' style='background:#2c5e3b; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Accedi con Google</a>";
    echo "</div>";
    exit;
}

$messaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['azione']) && $_POST['azione'] === 'aggiungi') {
        $nome = trim($_POST['nome'] ?? '');
        $descrizione = trim($_POST['descrizione'] ?? '');
        $sezione = $_POST['sezione'] ?? 'sacchetto';
        $quantita = (int)($_POST['quantita'] ?? 1);
        $pattern_id = 'pattern_' . time();

        $foto_nome = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $cartella_upload = __DIR__ . '/img/fabrics/';
            if (!file_exists($cartella_upload)) {
                mkdir($cartella_upload, 0777, true);
            }
            
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if ($ext === 'heic') {
                $messaggio = "<div class='alert error'>ATTENZIONE: Salva/esporta la foto in JPG o PNG per vederla nel browser!</div>";
            } else {
                $foto_nome = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], $cartella_upload . $foto_nome);
            }
        }

        if (!$messaggio) {
            try {
                $stmt = $pdo->prepare("INSERT INTO stoffe (sezione, nome, descrizione, quantita, pattern_id, foto) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$sezione, $nome, $descrizione, $quantita, $pattern_id, $foto_nome]);
                $messaggio = "<div class='alert success'>✓ Elemento aggiunto con successo al magazzino!</div>";
            } catch (PDOException $e) {
                $messaggio = "<div class='alert error'>Errore inserimento: " . $e->getMessage() . "</div>";
            }
        }
    }

    if (isset($_POST['azione']) && $_POST['azione'] === 'elimina') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM stoffe WHERE id = ?");
        $stmt->execute([$id]);
        $messaggio = "<div class='alert success'>✓ Elemento rimosso dal magazzino.</div>";
    }
}

$stoffe = [];
try {
    $stoffe = $pdo->query("SELECT * FROM stoffe ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $messaggio = "<div class='alert error'>Errore lettura database: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pannello Magazzino — Upcycled Lab</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f1ea; margin: 0; padding: 20px; color: #333; }
        .header-admin { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 25px; }
        h1, h2, h3 { margin-top: 0; color: #1a1a1a; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        label { font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 5px; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; }
        button.btn { background: #2c5e3b; color: white; border: none; padding: 12px 20px; font-size: 1rem; font-weight: bold; border-radius: 6px; cursor: pointer; }
        button.btn:hover { background: #21472c; }
        .btn-logout { background: #d32f2f; text-decoration: none; color: white; padding: 8px 15px; border-radius: 5px; font-size: 0.85rem; font-weight: bold; }
        .btn-delete { background: #e53935; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        th { background: #f8f9fa; font-size: 0.85rem; text-transform: uppercase; color: #666; }
        .img-preview { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }

        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert.success { background: #e8f5e9; color: #2e7d32; }
        .alert.error { background: #ffebee; color: #c62828; }
        .badge { padding:4px 10px; border-radius:12px; font-size:0.8rem; font-weight:bold; }
        .badge-sacchetto { background: #e0f2f1; color: #00695c; }
        .badge-acc { background: #fff3e0; color: #e65100; }
        .badge-cerniera { background: #f3e5f5; color: #6a1b9a; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-admin">
        <div>
            <h1 style="margin:0; font-size:1.5rem;">Gestione Magazzino Stoffe & Accessori</h1>
            <small style="color:#666;">Autenticato come Admin: <strong><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></strong></small>
        </div>
        <div>
            <a href="configuratore.php" style="margin-right: 15px; color: #2c5e3b; font-weight: bold; text-decoration: none;">Accedi al Configuratore &rarr;</a>
            <a href="reset.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <?= $messaggio ?>

    <div class="card">
        <h3>+ Aggiungi Materiale / Accessorio al Magazzino</h3>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="azione" value="aggiungi">
            
            <div class="form-grid">
                <div>
                    <label>Nome Materiale / Accessorio:</label>
                    <input type="text" name="nome" placeholder="es. Denim Vintage, Passante Spazzola Rosso, Etichetta Leather" required>
                </div>

                <div>
                    <label>Sezione Destinazione Specifico:</label>
                    <select name="sezione" required>
                        <option value="sacchetto">1. Stoffa per Sacchetto (Pannelli Principali)</option>
                        <option value="acc_pocket">2. Accessorio: Taschina Frontale</option>
                        <option value="acc_brush">3. Accessorio: Porta Spazzolina (Strisciolina)</option>
                        <option value="acc_tag">4. Accessorio: Etichetta in Pelle (Retro)</option>
                        <option value="cerniera">5. Colore Cerniera Zip</option>
                    </select>
                </div>

                <div>
                    <label>Quantità Disponibili:</label>
                    <input type="number" name="quantita" min="1" value="1" required>
                </div>

                <div>
                    <label>Foto / Campione (JPG / PNG):</label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required>
                </div>

                <div class="full-width">
                    <label>Breve Descrizione:</label>
                    <textarea name="descrizione" rows="2" placeholder="Note su materiale o colore..."></textarea>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn">Carica a Magazzino</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>Inventario Attuale (<?= count($stoffe) ?>)</h3>
        <?php if (count($stoffe) === 0): ?>
            <p style="color:#888;">Nessun elemento in magazzino.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Anteprima</th>
                        <th>Nome</th>
                        <th>Destinazione</th>
                        <th>Quantità</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stoffe as $st): ?>
                        <tr>
                            <td>
                                <?php if (!empty($st['foto']) && file_exists(__DIR__ . '/img/fabrics/' . $st['foto'])): ?>
                                    <img src="img/fabrics/<?= htmlspecialchars($st['foto']) ?>" class="img-preview" alt="Stoffa">
                                <?php else: ?>
                                    <span style="color:#aaa;">No Foto</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($st['nome']) ?></strong></td>
                            <td>
                                <?php 
                                    $sec = $st['sezione'];
                                    if ($sec === 'cerniera') echo '<span class="badge badge-cerniera">Cerniera Zip</span>';
                                    elseif (strpos($sec, 'acc_') === 0) echo '<span class="badge badge-acc">Accessorio ('.str_replace('acc_', '', $sec).')</span>';
                                    else echo '<span class="badge badge-sacchetto">Sacchetto</span>';
                                ?>
                            </td>
                            <td><strong><?= $st['quantita'] ?> pz</strong></td>
                            <td>
                                <form action="admin.php" method="POST" onsubmit="return confirm('Sicuro di voler eliminare?');">
                                    <input type="hidden" name="azione" value="elimina">
                                    <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                    <button type="submit" class="btn-delete">Elimina</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>