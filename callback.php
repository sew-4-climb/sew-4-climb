<?php
require_once __DIR__ . '/config/google_config.php';

if (!isset($_GET['code'])) {
    header('Location: index.php');
    exit;
}

$code = $_GET['code'];
$token_url = 'https://oauth2.googleapis.com/token';

$post_fields = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['access_token'])) {
    $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $data['access_token'];
    $user_info = json_decode(file_get_contents($user_info_url), true);

    $email = strtolower(trim($user_info['email']));
    $nome  = $user_info['name'];

    $_SESSION['user_email'] = $email;
    $_SESSION['user_name']  = $nome;

    if ($email === 'pietro.rogai09@gmail.com') {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
    } else {
        $_SESSION['is_admin'] = false;
        
        // Se c'è un ordine salvato dal configuratore, va al checkout, altrimenti al configuratore
        if (isset($_SESSION['carrello_configurazione'])) {
            header('Location: checkout.php');
        } else {
            header('Location: configuratore.php');
        }
    }
    exit;
} else {
    echo "Errore durante l'autenticazione con Google.";
}
?>