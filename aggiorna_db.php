<?php
require_once 'config/db.php';

try {
    // Aggiunge la colonna 'foto' se non esiste
    $pdo->exec("ALTER TABLE `stoffe` ADD COLUMN `foto` VARCHAR(255) DEFAULT NULL AFTER `pattern_id`");
    echo "<p style='color:green;'>✓ Colonna 'foto' aggiunta con successo!</p>";
} catch (Exception $e) {
    echo "<p style='color:orange;'>Info 'foto': " . $e->getMessage() . "</p>";
}

try {
    // Aggiunge la colonna 'descrizione' se non esiste
    $pdo->exec("ALTER TABLE `stoffe` ADD COLUMN `descrizione` TEXT DEFAULT NULL AFTER `nome`");
    echo "<p style='color:green;'>✓ Colonna 'descrizione' aggiunta con successo!</p>";
} catch (Exception $e) {
    echo "<p style='color:orange;'>Info 'descrizione': " . $e->getMessage() . "</p>";
}

echo "<h3>Operazione completata! Ora puoi cancellare questo file e usare admin.php</h3>";
?>
