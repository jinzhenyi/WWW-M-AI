<?php
session_start();
require_once 'config.php';
require_once 'includes/totp.php';

// 如果未开启登录，直接跳到聊天页
if (!ENABLE_LOGIN) {
    header('Location: chat.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    if (verifyTOTP(TOTP_SECRET, $code)) {
        $_SESSION['logged_in'] = true;
        header('Location: chat.php');
        exit;
    } else {
        $error = '验证码错误，请重试。';
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>TOTP 登录</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 60px auto; text-align: center; }
        input[type="text"] { width: 120px; padding: 8px; font-size: 16px; }
        button { padding: 8px 20px; font-size: 16px; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>输入动态验证码</h2>
    <form method="post">
        <input type="text" name="code" maxlength="6" placeholder="6位数字" required autofocus>
        <button type="submit">登录</button>
    </form>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <p style="margin-top:20px; color:#666;">使用 Google Authenticator 或 Microsoft Authenticator 生成</p>
</body>
</html>