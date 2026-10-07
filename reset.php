<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Svuota tutte le variabili di sessione (Admin, User email, Carrello)
$_SESSION = array();

// Cancella il cookie di sessione dal browser se presente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Distrugge la sessione attiva
session_destroy();

// Reindirizza l'utente direttamente alla Home Page principale
header('Location: index.php');
exit;
?>