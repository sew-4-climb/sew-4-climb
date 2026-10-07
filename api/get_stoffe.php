<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    // Recuperiamo tutte le stoffe attive
    $stmt = $pdo->query("SELECT * FROM stoffe WHERE attivo = 1");
    $stoffe = $stmt->fetchAll();

    // Organizziamo le stoffe per sezione
    $risultato = [
        'superiore' => [],
        'fondo'     => [],
        'lati'      => [],
        'taschina'  => []
    ];

    foreach ($stoffe as $stoffa) {
        $sezione = $stoffa['sezione'];
        if (array_key_exists($sezione, $risultato)) {
            $risultato[$sezione][] = [
                'id'         => $stoffa['id'],
                'nome'       => $stoffa['nome'],
                'pattern_id' => $stoffa['pattern_id'],
                'quantita'   => (int)$stoffa['quantita'],
                'colore_hex' => $stoffa['colore_hex']
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $risultato]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>