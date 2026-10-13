<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/google_config.php';
require_once __DIR__ . '/config/db.php';

// Se l'utente non è admin, lo reindirizziamo subito alla Home Page anziché bloccare l'URL su admin.php
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit;
}

$messaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Aggiungi nuovo materiale
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

        if (!$messaggio && isset($pdo) && $pdo !== null) {
            try {
                $stmt = $pdo->prepare("INSERT INTO stoffe (sezione, nome, descrizione, quantita, pattern_id, foto) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$sezione, $nome, $descrizione, $quantita, $pattern_id, $foto_nome]);
                $messaggio = "<div class='alert success'>✓ Elemento aggiunto con successo al magazzino!</div>";
            } catch (PDOException $e) {
                $messaggio = "<div class='alert error'>Errore inserimento: " . $e->getMessage() . "</div>";
            }
        }
    }

    // 2. Modifica quantità veloce (+ / -)
    if (isset($_POST['azione']) && $_POST['azione'] === 'aggiorna_quantita' && isset($pdo) && $pdo !== null) {
        $nome_mat = trim($_POST['nome_mat'] ?? '');
        $sezione_mat = trim($_POST['sezione_mat'] ?? '');
        $delta = (int)($_POST['delta'] ?? 0);

        if (!empty($nome_mat) && $delta !== 0) {
            try {
                // Recupera l'ID del record più recente per quel nome e sezione
                $stmt_get = $pdo->prepare("SELECT id, quantita FROM stoffe WHERE LOWER(TRIM(nome)) = LOWER(TRIM(?)) AND sezione = ? ORDER BY id DESC LIMIT 1");
                $stmt_get->execute([$nome_mat, $sezione_mat]);
                $rec = $stmt_get->fetch(PDO::FETCH_ASSOC);

                if ($rec) {
                    $nuova_qta = $rec['quantita'] + $delta;
                    if ($nuova_qta <= 0) {
                        // Se la quantità scende a 0 o meno, aggiorna a 0
                        $stmt_upd = $pdo->prepare("UPDATE stoffe SET quantita = 0 WHERE id = ?");
                        $stmt_upd->execute([$rec['id']]);
                    } else {
                        $stmt_upd = $pdo->prepare("UPDATE stoffe SET quantita = ? WHERE id = ?");
                        $stmt_upd->execute([$nuova_qta, $rec['id']]);
                    }
                }
            } catch (PDOException $e) {
                $messaggio = "<div class='alert error'>Errore aggiornamento quantità: " . $e->getMessage() . "</div>";
            }
        }
    }

    // 3. Elimina totale materiale
    if (isset($_POST['azione']) && $_POST['azione'] === 'elimina' && isset($pdo) && $pdo !== null) {
        $nome_elimina = trim($_POST['nome_elimina'] ?? '');
        $sezione_elimina = trim($_POST['sezione_elimina'] ?? '');
        
        try {
            $stmt = $pdo->prepare("DELETE FROM stoffe WHERE LOWER(TRIM(nome)) = LOWER(TRIM(?)) AND sezione = ?");
            $stmt->execute([$nome_elimina, $sezione_elimina]);
            $messaggio = "<div class='alert success'>✓ Materiale/Accessorio rimosso dal magazzino.</div>";
        } catch (PDOException $e) {
            $messaggio = "<div class='alert error'>Errore eliminazione: " . $e->getMessage() . "</div>";
        }
    }
}

$stoffe = [];
if (isset($pdo) && $pdo !== null) {
    try {
        $sql = "SELECT 
                    MAX(id) as id,
                    nome,
                    sezione,
                    SUM(quantita) as quantita,
                    MAX(foto) as foto
                FROM stoffe 
                GROUP BY LOWER(TRIM(nome)), sezione 
                ORDER BY id DESC";
        $stoffe = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $messaggio = "<div class='alert error'>Errore lettura database: " . $e->getMessage() . "</div>";
    }
}

// Suddivisione per settori
$gruppi = [
    'principali' => [],
    'cerniere'   => [],
    'accessori'  => []
];

foreach ($stoffe as $st) {
    $sec = $st['sezione'];
    if (in_array($sec, ['pannello_frontale', 'pannello_laterale', 'base', 'sacchetto'])) {
        $gruppi['principali'][] = $st;
    } elseif ($sec === 'cerniera') {
        $gruppi['cerniere'][] = $st;
    } else {
        $gruppi['accessori'][] = $st;
    }
}

// Funzione helper per stampare le tabelle divise per settore
function renderTabellaStoffe($lista) {
    if (empty($lista)) {
        echo "<p style='color:#888; margin: 15px 0;'>Nessun elemento presente in questo settore.</p>";
        return;
    }
    ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Anteprima</th>
                    <th>Nome</th>
                    <th>Destinazione</th>
                    <th>Quantità Totale</th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $st): ?>
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
                                elseif ($sec === 'pannello_frontale') echo '<span class="badge badge-sacchetto">Pannello Frontale</span>';
                                elseif ($sec === 'pannello_laterale') echo '<span class="badge badge-sacchetto">Pannello Laterale</span>';
                                elseif ($sec === 'base') echo '<span class="badge badge-sacchetto">Base / Fondo</span>';
                                elseif ($sec === 'acc_pocket') echo '<span class="badge badge-acc">Taschina Frontale</span>';
                                elseif ($sec === 'acc_brush') echo '<span class="badge badge-acc">Porta Spazzolina</span>';
                                elseif ($sec === 'acc_tag') echo '<span class="badge badge-acc">Etichetta Pelle</span>';
                                elseif (strpos($sec, 'acc_') === 0) echo '<span class="badge badge-acc">Accessorio ('.str_replace('acc_', '', $sec).')</span>';
                                else echo '<span class="badge badge-sacchetto">'.htmlspecialchars($sec).'</span>';
                            ?>
                        </td>
                        <td>
                            <div class="qty-controls">
                                <form action="admin.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="azione" value="aggiorna_quantita">
                                    <input type="hidden" name="nome_mat" value="<?= htmlspecialchars($st['nome']) ?>">
                                    <input type="hidden" name="sezione_mat" value="<?= htmlspecialchars($st['sezione']) ?>">
                                    <input type="hidden" name="delta" value="-1">
                                    <button type="submit" class="btn-qty" title="Riduci di 1 pz">-</button>
                                </form>

                                <span class="qty-number"><?= $st['quantita'] ?> pz</span>

                                <form action="admin.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="azione" value="aggiorna_quantita">
                                    <input type="hidden" name="nome_mat" value="<?= htmlspecialchars($st['nome']) ?>">
                                    <input type="hidden" name="sezione_mat" value="<?= htmlspecialchars($st['sezione']) ?>">
                                    <input type="hidden" name="delta" value="1">
                                    <button type="submit" class="btn-qty" title="Aumenta di 1 pz">+</button>
                                </form>
                            </div>
                        </td>
                        <td>
                            <form action="admin.php" method="POST" onsubmit="return confirm('Sicuro di voler eliminare tutti i pezzi di questo materiale?');">
                                <input type="hidden" name="azione" value="elimina">
                                <input type="hidden" name="nome_elimina" value="<?= htmlspecialchars($st['nome']) ?>">
                                <input type="hidden" name="sezione_elimina" value="<?= htmlspecialchars($st['sezione']) ?>">
                                <button type="submit" class="btn-delete">Elimina</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Magazzino — Upcycled Lab</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f1ea; margin: 0; padding: 20px; color: #333; }
        .header-admin { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .header-nav { display: flex; align-items: center; gap: 15px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 25px; }
        h1, h2, h3 { margin-top: 0; color: #1a1a1a; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        label { font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 5px; color: #555; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; }
        button.btn { background: #2c5e3b; color: white; border: none; padding: 14px 20px; font-size: 1rem; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; }
        button.btn:hover { background: #21472c; }
        .btn-logout { background: #d32f2f; text-decoration: none; color: white; padding: 10px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
        .btn-delete { background: #e53935; color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; }
        
        .table-wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; min-width: 600px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        th { background: #f8f9fa; font-size: 0.85rem; text-transform: uppercase; color: #666; }
        .img-preview { width: 65px; height: 65px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; display: block; }

        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert.success { background: #e8f5e9; color: #2e7d32; }
        .alert.error { background: #ffebee; color: #c62828; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; white-space: nowrap; }
        .badge-sacchetto { background: #e0f2f1; color: #00695c; }
        .badge-acc { background: #fff3e0; color: #e65100; }
        .badge-cerniera { background: #f3e5f5; color: #6a1b9a; }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #2c5e3b;
            padding-bottom: 8px;
            margin-bottom: 15px;
            margin-top: 10px;
        }
        .section-header h3 { margin: 0; color: #2c5e3b; font-size: 1.2rem; }

        /* Controlli Quantità Veloci (+/-) */
        .qty-controls {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #f0f0f0;
            padding: 6px 12px;
            border-radius: 25px;
            border: 1px solid #ddd;
        }

        .btn-qty {
            background: white;
            border: 1px solid #ccc;
            color: #333;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            line-height: 1;
            padding: 0;
            touch-action: manipulation;
        }

        .btn-qty:active, .btn-qty:hover {
            background: #2c5e3b;
            color: white;
            border-color: #2c5e3b;
        }

        .qty-number {
            font-weight: 800;
            font-size: 1rem;
            min-width: 40px;
            text-align: center;
        }

        /* --- OTTIMIZZAZIONE RESPONSIVE PER MOBILE --- */
        @media (max-width: 768px) {
            body { padding: 12px; }
            .header-admin { padding: 15px; flex-direction: column; align-items: flex-start; }
            .header-nav { width: 100%; justify-content: space-between; margin-top: 10px; }
            .card { padding: 16px; }
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .btn-delete { padding: 10px 14px; width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-admin">
        <div>
            <h1 style="margin:0; font-size:1.3rem;">
                <a href="index.php" style="text-decoration:none; color:inherit;">SEW 4 CLIMB — Magazzino</a>
            </h1>
            <small style="color:#666;">Autenticato come Admin: <strong><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></strong></small>
        </div>
        <div class="header-nav">
            <a href="index.php" style="color: #2c5e3b; font-weight: bold; text-decoration: none; font-size:0.9rem;">&larr; Home</a>
            <a href="configuratore.php" style="color: #2c5e3b; font-weight: bold; text-decoration: none; font-size:0.9rem;">Configuratore &rarr;</a>
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
                        <option value="pannello_frontale">1. Stoffa: Pannelli Frontali (Fronte/Retro)</option>
                        <option value="pannello_laterale">2. Stoffa: Pannelli Laterali (Lati Etnici)</option>
                        <option value="base">3. Stoffa: Base / Fondo Sacchetto</option>
                        <option value="acc_pocket">4. Accessorio: Taschina Frontale</option>
                        <option value="acc_brush">5. Accessorio: Porta Spazzolina (Strisciolina)</option>
                        <option value="acc_tag">6. Accessorio: Etichetta in Pelle (Retro)</option>
                        <option value="cerniera">7. Colore Cerniera Zip</option>
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

    <!-- INVENTARIO DIVISO PER SETTORI -->
    <div class="card">
        <h2>Inventario Attuale per Settore (<?= count($stoffe) ?> elementi)</h2>
        
        <!-- SETTORE PANNELLI PRINCIPALI E BASI -->
        <div class="section-header">
            <h3>🧵 Settore Pannelli Principali & Basi (<?= count($gruppi['principali']) ?>)</h3>
        </div>
        <?php renderTabellaStoffe($gruppi['principali']); ?>

        <!-- SETTORE CERNIERE -->
        <div class="section-header" style="margin-top: 35px;">
            <h3>🤐 Settore Cerniere Zip (<?= count($gruppi['cerniere']) ?>)</h3>
        </div>
        <?php renderTabellaStoffe($gruppi['cerniere']); ?>

        <!-- SETTORE ACCESSORI -->
        <div class="section-header" style="margin-top: 35px;">
            <h3>✂️ Settore Accessori (<?= count($gruppi['accessori']) ?>)</h3>
        </div>
        <?php renderTabellaStoffe($gruppi['accessori']); ?>
    </div>
</div>

</body>
</html>