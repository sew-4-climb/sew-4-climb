<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/google_config.php';

// Salva la pagina da cui proviene l'utente per riportarlo esattamente lì dopo il login
if (isset($_GET['from']) && !empty($_GET['from'])) {
    $_SESSION['redirect_after_login'] = filter_var($_GET['from'], FILTER_SANITIZE_URL);
} else {
    // Se non viene specificato un parametro, torna alla Home
    $_SESSION['redirect_after_login'] = 'index.php';
}

$params = [
    'response_type' => 'code',
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
    'access_type'   => 'online',
    'prompt'        => 'select_account'
];

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit;
?>