<?php
session_start();
require_once 'config.php';

// 开启登录时，必须检查 session
if (ENABLE_LOGIN && empty($_SESSION['logged_in'])) {
    http_response_code(403);
    echo json_encode(['reply' => '未授权']);
    exit;
}

if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

$API_KEY = "你的MiMo_API_Key";
$URL = "https://api.mimo.xiaomi.com/v1/chat/completions";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userMsg = trim($input['prompt'] ?? '');

    if ($userMsg === '') {
        echo json_encode(['reply' => '请输入问题']);
        exit;
    }

    $_SESSION['messages'][] = ['role' => 'user', 'content' => $userMsg];

    $ch = curl_init($URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $API_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'mimo-v2.5',
        'messages' => $_SESSION['messages'],
        'temperature' => 0.7
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $reply = $data['choices'][0]['message']['content'] ?? 'API 错误';

    $_SESSION['messages'][] = ['role' => 'assistant', 'content' => $reply];

    echo json_encode(['reply' => $reply]);
    exit;
}
?>