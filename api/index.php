<?php

$botToken = '7216903142:AAHFi2d9vVOmUa3lWLBWPfVzt6t4W3AU5Rw';
$apiUrl = "https://api.telegram.org/bot$botToken/";
$webhookUrl = 'https://codethemes.vercel.app';

function sendMessage($chatId, $message) {
    global $apiUrl;
    $url = $apiUrl . "sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}

function setWebhook($apiUrl, $webhookUrl) {
    $url = $apiUrl . 'setWebhook?url=' . urlencode($webhookUrl);
    $response = file_get_contents($url);
    return $response;
}

// Configura o webhook na primeira execução
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = setWebhook($apiUrl, $webhookUrl);
    echo "Webhook configurado: " . $result;
    exit;
}

$update = file_get_contents("php://input");
$updateArray = json_decode($update, true);

if (isset($updateArray['message'])) {
    $message = $updateArray['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'];

    if (preg_match('/^\/sn\s+(\S+)/', $text, $matches)) {
        $sn = $matches[1];
        $url = 'https://script.google.com/macros/s/AKfycbwVL_A2dJyq04tlasJk5-joNf2j22FhoDyHjD_XpBTh5F2FypkLpTfiwf7Q33Mb5b0arQ/exec';

        $params = [
            'page' => 'test',
            'columnID' => 'SN',
            'search' => $sn,
            'user' => 'example_user',
            'date' => date('Y-m-d H:i:s'),
            'url' => 'https://docs.google.com/spreadsheets/d/1tsw-O6LJ-NC3nQ4rlrQcpUUnRGBG6M3dkYQ6XPK4IXg/edit?'
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($params),
            ],
        ];

        $context  = stream_context_create($options);

        // Faz a requisição POST e obtém a resposta
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            sendMessage($chatId, "Erro ao acessar o App Script.");
            exit;
        }

        $response = json_decode($result, true);

        if (isset($response['status']) && $response['status'] === 200 && isset($response['password'])) {
            sendMessage($chatId, "Senha: " . $response['password']);
        } else {
            sendMessage($chatId, "Não encontrei o SN fornecido ou houve um erro na resposta.");
        }
    } else {
        sendMessage($chatId, "Comando inválido. Use /sn {sn} para procurar a senha.");
    }
} else {
    echo "Método não permitido.";
}
?>
