<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';

// Salvataggio configurazione nel carrello e redirect alla Home
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione_carrello'])) {
    $config_json =$_POST['configurazione_json'] ?? '{}';
    $config_data = json_decode($config_json, true);

    if (!isset($_SESSION['cart'])) {$_SESSION['cart'] = [];
    }

    $_SESSION['cart'][] = [
        'id' => time(),
        'nome' => 'Chalk Bag Custom Sew 4 Climb',
        'prezzo' => 45.00,
        'dettagli' => $config_data
    ];

    header('Location: index.php?added=1');
    exit;
}

$stoffe = [];
if (isset($pdo)) {
    try {
        $stmt =$pdo->query("SELECT * FROM stoffe WHERE quantita > 0 ORDER BY id DESC");
        $stoffe =$stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {$stoffe = [];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuratore Custom — Sew 4 Climb</title>
    <style>
        .fabric-card {
    position: relative;
}

.fabric-stock-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
    color: white;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: 10px;
    z-index: 2;
}

.fabric-stock-badge.low {
    background: #e65100;
}

.fabric-card.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none;
    filter: grayscale(0.8);
}
        :root {
            --primary: #2c5e3b;
            --primary-dark: #1e4228;
            --bg-cream: #f4f1ea;
            --text-dark: #1a1a1a;
            --text-muted: #666666;
            --card-radius: 20px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #f4f1ea 0%, #e8e2d5 100%);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
            height: 100vh;
            overflow: hidden;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 15px 35px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            position: relative;
            z-index: 10;
        }

        .logo {
            font-weight: 900;
            font-size: 1.3rem;
            letter-spacing: 1.5px;
            color: var(--text-dark);
            text-decoration: none;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .cart-icon {
            position: relative;
            text-decoration: none;
            display: flex;
            align-items: center;
            color: var(--text-dark);
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

        .main-container {
            display: grid;
            grid-template-columns: 1fr 440px;
            height: calc(100vh - 65px);
            padding: 20px;
            gap: 20px;
        }

        .preview-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: var(--card-radius);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        }

        .bag-stage {
            position: relative;
            width: 380px;
            height: 480px;
        }

        .bag-stage svg {
            width: 100%;
            height: 100%;
            filter: drop-shadow(0px 15px 25px rgba(0,0,0,0.18));
        }

        .interactive-part {
            cursor: pointer;
            transition: opacity 0.2s, stroke 0.2s;
        }

        .interactive-part:hover {
            opacity: 0.88;
            stroke: #2c5e3b;
            stroke-width: 3.5px;
        }

        .view-controls {
            position: absolute;
            top: 25px;
            left: 25px;
            display: flex;
            gap: 10px;
        }

        .btn-view {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.08);
            padding: 10px 16px;
            border-radius: 30px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            background: white;
        }

        .btn-view.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 6px 18px rgba(44, 94, 59, 0.3);
        }

        .btn-view.active svg { stroke: white; }

        .hint-banner {
            position: absolute;
            bottom: 25px;
            background: rgba(26, 26, 26, 0.78);
            backdrop-filter: blur(10px);
            color: white;
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: var(--card-radius);
            border: 1px solid rgba(255, 255, 255, 0.8);
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        h2 {
            font-size: 1.15rem;
            margin-top: 0;
            margin-bottom: 12px;
            color: var(--text-dark);
            font-weight: 800;
        }

        .active-info {
            background: rgba(44, 94, 59, 0.1);
            border: 1px solid rgba(44, 94, 59, 0.25);
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .grid-fabric {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .fabric-card {
            border: 2px solid rgba(0, 0, 0, 0.06);
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            padding: 6px;
            background: rgba(255, 255, 255, 0.9);
        }

        .fabric-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }

        .fabric-card.selected {
            border-color: var(--primary);
            background: rgba(44, 94, 59, 0.08);
        }

        .fabric-img {
            width: 100%;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
        }

        .fabric-name {
            font-size: 0.75rem;
            font-weight: 700;
            margin-top: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #444;
        }

        .btn-remove-acc {
            background: rgba(229, 57, 53, 0.08);
            color: #c62828;
            border: 1px dashed rgba(229, 57, 53, 0.4);
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            width: 100%;
            margin-bottom: 18px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-remove-acc:hover { background: rgba(229, 57, 53, 0.15); }

        .btn-order {
            background: var(--primary);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 35px;
            font-size: 1.05rem;
            font-weight: 800;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 8px 20px rgba(44, 94, 59, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-order:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(44, 94, 59, 0.4);
        }

        .empty-msg {
            color: var(--text-muted);
            font-size: 0.88rem;
            grid-column: span 3;
            padding: 22px;
            text-align: center;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 14px;
            border: 1px dashed rgba(0, 0, 0, 0.15);
            line-height: 1.5;
        }

        /* --- MODALE E TOAST CUSTOM BRANDED --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-card {
            background: #f4f1ea;
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            width: 90%;
            max-width: 450px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(20px);
            transition: transform 0.3s ease;
            text-align: center;
        }

        .modal-overlay.active .modal-card {
            transform: translateY(0);
        }

        .modal-icon-badge {
            width: 56px;
            height: 56px;
            background: rgba(229, 57, 53, 0.12);
            color: #d32f2f;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0 0 10px 0;
        }

        .modal-sub {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 18px;
            line-height: 1.4;
        }

        .modal-list {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 14px;
            padding: 12px 18px;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 600;
            color: #333;
            max-height: 180px;
            overflow-y: auto;
            margin-bottom: 22px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .modal-list ul {
            margin: 0;
            padding-left: 20px;
        }

        .modal-list li {
            margin-bottom: 6px;
        }

        .modal-list li:last-child {
            margin-bottom: 0;
        }

        .btn-modal-close {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 30px;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s ease;
        }

        .btn-modal-close:hover {
            background: var(--primary-dark);
        }

        /* Toast di notifica rapida */
        .toast-banner {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--text-dark);
            color: white;
            padding: 12px 22px;
            border-radius: 30px;
            font-size: 0.88rem;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 999;
            opacity: 0;
            transform: translateY(20px);
            pointer-events: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toast-banner.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

<div class="header">
    <a href="index.php" class="logo">SEW 4 CLIMB</a>
    <div class="nav-right">
        <?php if (isset($_SESSION['is_admin']) &&$_SESSION['is_admin'] === true): ?>
            <a href="admin.php" style="color:var(--primary); font-weight:bold; font-size:0.9rem; text-decoration:none;">&larr; Magazzino Admin</a>
        <?php endif; ?>
        
        <a href="cart.php" class="cart-icon" aria-label="Carrello">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <?php if (!empty($_SESSION['cart'])): ?>
                <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
            <?php endif; ?>
        </a>

        <?php if (isset($_SESSION['user_email'])): ?>
            <a href="reset.php" style="color:#888; font-size:0.9rem; text-decoration:none;">Logout</a>
        <?php else: ?>
            <a href="login_google.php" style="color:var(--primary); font-weight:bold; font-size:0.9rem; text-decoration:none;">Accedi</a>
        <?php endif; ?>
    </div>
</div>

<div class="main-container">
    <div class="preview-container">
        <!-- VISTE TRIDIMENSIONALI (FRONTE, RETRO, DAL BASSO) -->
        <div class="view-controls">
            <button class="btn-view active" id="btnFront" onclick="switchView('front')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                Fronte
            </button>
            <button class="btn-view" id="btnBack" onclick="switchView('back')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                Retro (Zip & Leather)
            </button>
            <button class="btn-view" id="btnBottom" onclick="switchView('bottom')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="12" rx="10" ry="6"/></svg>
                Dal Basso (Fondo)
            </button>
        </div>

        <div class="bag-stage">
            <svg viewBox="0 0 400 500" id="svgBag">
                <defs>
                    <pattern id="pat_top_front" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#78909c"/></pattern>
                    <pattern id="pat_top_back" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#78909c"/></pattern>
                    <pattern id="pat_bottom_front" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#263238"/></pattern>
                    <pattern id="pat_bottom_back" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#263238"/></pattern>
                    <!-- BASE DEL FONDO SEPARATA E INDIPENDENTE -->
                    <pattern id="pat_bottom_base" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#1e272c"/></pattern>
                    <pattern id="pat_side_left" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#5d4037"/></pattern>
                    <pattern id="pat_side_right" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#5d4037"/></pattern>
                    <pattern id="pat_pocket" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#455a64"/></pattern>
                    <pattern id="pat_brush" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#78909c"/></pattern>
                    <pattern id="pat_tag" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#8d6e63"/></pattern>
                    <pattern id="pat_zipper" patternUnits="userSpaceOnUse" width="100" height="100"><rect width="100" height="100" fill="#111111"/></pattern>
                </defs>

                <!-- VISTA FRONTE -->
                <g id="groupFront">
                    <path class="interactive-part" d="M 45,70 L 80,75 L 65,420 L 30,410 Z" fill="url(#pat_side_left)" stroke="#333" stroke-width="1.5" onclick="selectPart('side_left', 'Fianco Sinistro Etnico', 'sacchetto')"/>
                    <path class="interactive-part" d="M 320,75 L 355,70 L 370,410 L 335,420 Z" fill="url(#pat_side_right)" stroke="#333" stroke-width="1.5" onclick="selectPart('side_right', 'Fianco Destro Etnico', 'sacchetto')"/>

                    <path class="interactive-part" d="M 80,75 L 320,75 L 335,250 L 65,250 Z" fill="url(#pat_top_front)" stroke="#333" stroke-width="1.5" onclick="selectPart('top_front', 'Parte Superiore Denim (Fronte)', 'sacchetto')"/>
                    <line x1="200" y1="75" x2="200" y2="250" stroke="#37474f" stroke-width="2" stroke-dasharray="4,3"/>

                    <path class="interactive-part" d="M 65,250 L 335,250 L 335,420 L 65,420 Z" fill="url(#pat_bottom_front)" stroke="#333" stroke-width="1.5" onclick="selectPart('bottom_front', 'Fondo Inferiore (Fronte)', 'sacchetto')"/>

                    <g id="element_pocket">
                        <path class="interactive-part" d="M 100,105 L 180,105 L 180,185 L 100,185 Z" fill="url(#pat_pocket)" stroke="#263238" stroke-width="2" onclick="selectPart('pocket', 'Accessorio: Taschina Frontale', 'acc_pocket')"/>
                        <circle cx="106" cy="111" r="3.5" fill="#b87333" stroke="#5d4037"/>
                        <circle cx="174" cy="111" r="3.5" fill="#b87333" stroke="#5d4037"/>
                    </g>

                    <g id="element_brush">
                        <rect class="interactive-part" x="210" y="325" width="75" height="18" rx="3" fill="url(#pat_brush)" stroke="#111" stroke-width="1.5" onclick="selectPart('brush', 'Accessorio: Porta Spazzolina', 'acc_brush')"/>
                    </g>

                    <path d="M 70,60 L 330,60 L 320,75 L 80,75 Z" fill="#37474f" stroke="#111"/>
                    <rect x="325" y="55" width="22" height="28" rx="4" fill="#111"/>
                </g>

                <!-- VISTA RETRO -->
                <g id="groupBack" style="display: none;">
                    <path class="interactive-part" d="M 45,70 L 80,75 L 65,420 L 30,410 Z" fill="url(#pat_side_left)" stroke="#333" stroke-width="1.5" onclick="selectPart('side_left', 'Fianco Sinistro Etnico', 'sacchetto')"/>
                    <path class="interactive-part" d="M 320,75 L 355,70 L 370,410 L 335,420 Z" fill="url(#pat_side_right)" stroke="#333" stroke-width="1.5" onclick="selectPart('side_right', 'Fianco Destro Etnico', 'sacchetto')"/>

                    <path class="interactive-part" d="M 80,75 L 320,75 L 335,250 L 65,250 Z" fill="url(#pat_top_back)" stroke="#333" stroke-width="1.5" onclick="selectPart('top_back', 'Parte Superiore Denim (Retro)', 'sacchetto')"/>

                    <g id="element_tag" class="interactive-part" onclick="selectPart('tag', 'Accessorio: Etichetta in Pelle', 'acc_tag')">
                        <rect x="140" y="95" width="120" height="75" rx="4" fill="url(#pat_tag)" stroke="#4e342e" stroke-width="2"/>
                        <text x="200" y="125" font-size="11" font-weight="bold" fill="#3e2723" text-anchor="middle">LEVI STRAUSS & CO.</text>
                    </g>

                    <path class="interactive-part" d="M 65,250 L 335,250 L 335,420 L 65,420 Z" fill="url(#pat_bottom_back)" stroke="#333" stroke-width="1.5" onclick="selectPart('bottom_back', 'Fondo Inferiore (Retro)', 'sacchetto')"/>

                    <g class="interactive-part" onclick="selectPart('zipper', 'Colore Cerniera Zip', 'cerniera')">
                        <rect x="100" y="280" width="200" height="14" rx="2" fill="url(#pat_zipper)"/>
                        <line x1="105" y1="287" x2="295" y2="287" stroke="#ccc" stroke-width="2" stroke-dasharray="3,2"/>
                        <rect x="250" y="276" width="10" height="22" rx="2" fill="#cfd8dc" stroke="#37474f"/>
                    </g>

                    <path d="M 70,60 L 330,60 L 320,75 L 80,75 Z" fill="#37474f" stroke="#111"/>
                    <rect x="53" y="55" width="22" height="28" rx="4" fill="#111"/>
                </g>

                <!-- VISTA DAL BASSO (BASE INFERIORE DEL FONDO INDIPENDENTE) -->
                <g id="groupBottom" style="display: none;">
                    <rect x="40" y="120" width="320" height="260" rx="60" fill="#1a1a1a" stroke="#333" stroke-width="3"/>
                    
                    <!-- PANNELLO BASE DEL FONDO INDIPENDENTE -->
                    <rect class="interactive-part" x="55" y="135" width="290" height="230" rx="45" fill="url(#pat_bottom_base)" stroke="#2c5e3b" stroke-width="3" onclick="selectPart('bottom_base', 'Base del Fondo (Vista dal Basso)', 'sacchetto')"/>
                    
                    <rect x="65" y="145" width="270" height="210" rx="38" fill="none" stroke="#e0e0e0" stroke-width="2" stroke-dasharray="6,4"/>
                    
                    <text x="200" y="255" font-size="14" font-weight="bold" fill="#ffffff" text-anchor="middle" style="letter-spacing:1px; opacity:0.85;">BASE DEL FONDO INDIPENDENTE</text>
                </g>
            </svg>
        </div>

        <div class="hint-banner">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
            Clicca su un elemento per personalizzarlo o rimuoverlo
        </div>
    </div>

    <div class="panel">
        <div>
            <h2>Elemento Selezionato:</h2>
            <div class="active-info" id="activePartLabel">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                <span>Parte Superiore Denim (Fronte)</span>
            </div>

            <div id="btnRemoveContainer" style="display: none;">
                <button class="btn-remove-acc" onclick="removeSelectedAccessory()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Non inserire questo accessorio
                </button>
            </div>

            <h2>Scegli Materiale dal Magazzino</h2>
            <div class="grid-fabric" id="fabricGrid"></div>
        </div>

        <form method="POST" action="configuratore.php" style="margin-top: 20px;" onsubmit="return prepareCartData()">
            <input type="hidden" name="azione_carrello" value="1">
            <input type="hidden" name="configurazione_json" id="configJsonInput">
            <button type="submit" class="btn-order">
                <span>Salva e Aggiungi al Carrello</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>
    </div>
</div>

<!-- POPUP MODALE BRANDED -->
<div class="modal-overlay" id="customValidationModal">
    <div class="modal-card">
        <div class="modal-icon-badge">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h3 class="modal-title">Configurazione Incompleta</h3>
        <p class="modal-sub">Seleziona una stoffa o rifiuta l'accessorio per le seguenti sezioni prima di procedere:</p>
        <div class="modal-list">
            <ul id="missingItemsList"></ul>
        </div>
        <button type="button" class="btn-modal-close" onclick="closeValidationModal()">Ho Capito, Completa</button>
    </div>
</div>

<!-- TOAST DI CONFERMA ESCLUSIONE ACCESSORIO -->
<div class="toast-banner" id="toastNotification">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toastMessage">Accessorio escluso dal sacchetto</span>
</div>

<script>
const allStoffe = <?= json_encode($stoffe); ?>;

let currentSelectedPart = 'top_front';
let currentCategory = 'sacchetto';
let userSelections = {}; // Mappa: { 'top_front': { id: 5, nome: 'Denim' }, ... }

function selectPart(partId, partLabel, targetCategory) {
    currentSelectedPart = partId;
    currentCategory = targetCategory;
    
    document.getElementById('activePartLabel').innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        <span>${partLabel}</span>
    `;

    const isAccessory = partId === 'pocket' || partId === 'brush' || partId === 'tag';
    document.getElementById('btnRemoveContainer').style.display = isAccessory ? 'block' : 'none';

    const elem = document.getElementById('element_' + partId);
    if (elem) elem.style.display = 'block';

    renderFabricGrid();
}

// Calcola le quantità utilizzate attualmente nella configurazione
function getUsedQuantity(fabricId) {
    let used = 0;
    for (let part in userSelections) {
        if (userSelections[part] && userSelections[part].id === fabricId) {
            used++;
        }
    }
    return used;
}

function renderFabricGrid() {
    const grid = document.getElementById('fabricGrid');
    grid.innerHTML = '';

    let filtered = [];

    if (currentCategory === 'sacchetto') {
        filtered = allStoffe.filter(s => s.sezione === 'sacchetto' || s.sezione === 'superiore' || s.sezione === 'fondo' || s.sezione === 'lati' || !s.sezione);
    } else {
        filtered = allStoffe.filter(s => s.sezione === currentCategory);
    }

    if (filtered.length === 0) {
        grid.innerHTML = `<div class="empty-msg">I materiali per questa sezione verranno riforniti a breve.<br><small style="color:#888;">Riprova prossimamente per scoprire le nuove stoffe disponibili!</small></div>`;
        return;
    }

    filtered.forEach(item => {
        const usedCount = getUsedQuantity(item.id);
        const currentPartSelectedFabric = userSelections[currentSelectedPart];
        
        // Se questa stoffa è già usata per la sezione corrente, non contiamo quell'uso ai fini del calcolo residuo
        const isSelectedForCurrentPart = currentPartSelectedFabric && currentPartSelectedFabric.id === item.id;
        const effectiveUsed = isSelectedForCurrentPart ? usedCount - 1 : usedCount;
        
        const availableStock = parseInt(item.quantita) - effectiveUsed;

        const card = document.createElement('div');
        card.className = 'fabric-card' + (availableStock <= 0 ? ' disabled' : '') + (isSelectedForCurrentPart ? ' selected' : '');
        card.onclick = function() { 
            if (availableStock > 0) {
                applyFabricToSelected(item, this); 
            } else {
                showToast('Quantità esaurite per questo materiale!');
            }
        };

        let imgHtml = item.foto 
            ? `<img src="img/fabrics/${item.foto}" class="fabric-img" alt="${item.nome}">`
            : `<div class="fabric-img" style="background:#ccc; display:flex; align-items:center; justify-content:center; font-size:0.7rem;">No foto</div>`;

        const badgeClass = availableStock <= 1 ? 'fabric-stock-badge low' : 'fabric-stock-badge';
        const badgeHtml = `<div class="${badgeClass}">${availableStock} pz</div>`;

        card.innerHTML = `${badgeHtml}${imgHtml}<div class="fabric-name">${item.nome}</div>`;
        grid.appendChild(card);
    });
}

function removeSelectedAccessory() {
    const elem = document.getElementById('element_' + currentSelectedPart);
    if (elem) {
        elem.style.display = 'none';
        userSelections[currentSelectedPart] = { id: null, nome: 'Nessuno' };
        showToast('Accessorio escluso dal sacchetto!');
        renderFabricGrid();
    }
}

function showToast(msg) {
    const toast = document.getElementById('toastNotification');
    document.getElementById('toastMessage').innerText = msg;
    toast.classList.add('active');
    setTimeout(() => {
        toast.classList.remove('active');
    }, 3000);
}

function switchView(view) {
    document.getElementById('btnFront').classList.remove('active');
    document.getElementById('btnBack').classList.remove('active');
    document.getElementById('btnBottom').classList.remove('active');

    document.getElementById('groupFront').style.display = 'none';
    document.getElementById('groupBack').style.display = 'none';
    document.getElementById('groupBottom').style.display = 'none';

    if (view === 'front') {
        document.getElementById('groupFront').style.display = 'block';
        document.getElementById('btnFront').classList.add('active');
    } else if (view === 'back') {
        document.getElementById('groupBack').style.display = 'block';
        document.getElementById('btnBack').classList.add('active');
    } else if (view === 'bottom') {
        document.getElementById('groupBottom').style.display = 'block';
        document.getElementById('btnBottom').classList.add('active');
        selectPart('bottom_base', 'Base del Fondo (Vista dal Basso)', 'sacchetto');
    }
}

function applyFabricToSelected(item, element) {
    if (!item.foto) return;

    // Salviamo ID e Nome della stoffa scelta per questa sezione
    userSelections[currentSelectedPart] = { id: item.id, nome: item.nome };

    const elem = document.getElementById('element_' + currentSelectedPart);
    if (elem) elem.style.display = 'block';

    const patternId = 'pat_' + currentSelectedPart;
    const patternElem = document.getElementById(patternId);

    if (patternElem) {
        patternElem.innerHTML = `<image href="img/fabrics/${item.foto}" width="100" height="100" preserveAspectRatio="xMidYMid slice"/>`;
    }

    // Ri-renderizziamo la griglia per aggiornare i contatori dei badge
    renderFabricGrid();
}

function prepareCartData() {
    const requiredParts = [
        'top_front', 'top_back', 'bottom_front', 'bottom_back', 'bottom_base', 'side_left', 'side_right'
    ];
    const accessories = ['pocket', 'brush', 'tag'];

    const partLabels = {
        'top_front': 'Parte Superiore (Fronte)',
        'top_back': 'Parte Superiore (Retro)',
        'bottom_front': 'Fondo Inferiore (Fronte)',
        'bottom_back': 'Fondo Inferiore (Retro)',
        'bottom_base': 'Base del Fondo',
        'side_left': 'Fianco Sinistro Etnico',
        'side_right': 'Fianco Destro Etnico',
        'pocket': 'Accessorio: Taschina Frontale',
        'brush': 'Accessorio: Porta Spazzolina',
        'tag': 'Accessorio: Etichetta in Pelle'
    };

    let missing = [];

    requiredParts.forEach(part => {
        if (!userSelections[part] || !userSelections[part].nome || userSelections[part].nome === 'Nessuno') {
            missing.push(partLabels[part] || part);
        }
    });

    accessories.forEach(acc => {
        if (!userSelections.hasOwnProperty(acc)) {
            missing.push(partLabels[acc] || acc);
        }
    });

    if (missing.length > 0) {
        showValidationModal(missing);
        return false;
    }

    // Formattiamo i dati per inviare solo i nomi al carrello PHP
    let finalPayload = {};
    for (let p in userSelections) {
        finalPayload[p] = userSelections[p].nome;
    }

    document.getElementById('configJsonInput').value = JSON.stringify(finalPayload);
    return true;
}

window.onload = function() {
    renderFabricGrid();
};
</script>

</body>
</html>