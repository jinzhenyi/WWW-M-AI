<?php
session_start();
require_once 'config.php';

// 如果开启了登录，但用户未登录，则跳转到登录页
if (ENABLE_LOGIN && empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>对话</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 30px auto; }
        #chat { border: 1px solid #ccc; height: 400px; overflow-y: auto; padding: 10px; margin-bottom: 10px; }
        .user { color: blue; }
        .ai { color: green; }
    </style>
</head>
<body>
    <h2>ai 对话</h2>
    <div id="chat"></div>
    <input type="text" id="userInput" placeholder="输入问题..." style="width: 80%;" />
    <button onclick="sendMsg()">发送</button>

    <script>
        function addLine(who, text) {
            const chat = document.getElementById('chat');
            const p = document.createElement('p');
            p.className = who;
            p.textContent = (who === 'user' ? '你: ' : 'ai: ') + text;
            chat.appendChild(p);
            chat.scrollTop = chat.scrollHeight;
        }

        async function sendMsg() {
            const input = document.getElementById('userInput');
            const prompt = input.value.trim();
            if (!prompt) return;
            input.value = '';

            addLine('user', prompt);

            try {
                const resp = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: prompt })
                });
                if (resp.status === 403) {
                    addLine('ai', '登录已过期，请刷新页面重新登录');
                    return;
                }
                const data = await resp.json();
                addLine('ai', data.reply);
            } catch (err) {
                addLine('ai', '错误: ' + err.message);
            }
        }

        document.getElementById('userInput').addEventListener('keypress', e => {
            if (e.key === 'Enter') sendMsg();
        });
    </script>
</body>
</html>