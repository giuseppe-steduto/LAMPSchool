<?php
$token = "987901422:AAG-T0WEzGDy_jYqfe5e2xNqEh0PPXUcv3g"; //Token bot Telegram
$urlDestinazione = "https://peppebocci.altervista.org/registro/lampschool/registrazioneOTP/registra.php";
$data = array("url" => $urlDestinazione);
$data = json_encode($data);
$url = "https://api.telegram.org/bot" . $token . "/setWebhook";
$options = array(
    'http' => array(
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => $data
    )
);
$context  = stream_context_create($options);
$r = file_get_contents($url, false, $context);
echo $r;
 ?>
