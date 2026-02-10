<?php
/**
 * 沐辰网络 amuchen.com - 订阅接口
 *
 * 功能：接收邮箱 → 保存到 JSON → 通过 SMTP 发通知邮件
 * 部署：放到网站根目录，确保 PHP 有写入权限
 */

// ====== SMTP 配置 ======
define('SMTP_HOST', 'smtp.qq.com');           // QQ邮箱SMTP服务器
define('SMTP_PORT', 465);
define('SMTP_USER', 'your_email@qq.com');     // 你的QQ邮箱
define('SMTP_PASS', 'your_smtp_auth_code');   // QQ邮箱SMTP授权码
define('SMTP_FROM', 'your_email@qq.com');     // 发件人（同上）
define('SMTP_FROM_NAME', '沐辰网络');
define('ADMIN_EMAIL', 'your_admin@qq.com');   // 接收通知的管理员邮箱

// ====== ShowDoc 微信推送配置 ======
define('SHOWDOC_PUSH_URL', 'your_showdoc_push_url');

// 响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '仅支持 POST 请求']);
    exit;
}

// 数据文件
$dataFile = __DIR__ . '/subscribers.json';

// 读取请求
$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? trim($input['email']) : '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '请输入有效的邮箱地址']);
    exit;
}

// 读取已有数据
$subscribers = [];
if (file_exists($dataFile)) {
    $subscribers = json_decode(file_get_contents($dataFile), true) ?: [];
}

// 去重
foreach ($subscribers as $sub) {
    if (strtolower($sub['email']) === strtolower($email)) {
        echo json_encode(['success' => true, 'message' => '该邮箱已订阅', 'isNew' => false]);
        exit;
    }
}

// 新增
$subscribers[] = [
    'email' => $email,
    'time'  => date('Y-m-d H:i:s'),
    'ip'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
];

// 写入文件
$written = file_put_contents(
    $dataFile,
    json_encode($subscribers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($written === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '保存失败']);
    exit;
}

// ====== 1. 通过 ShowDoc 推送微信通知给管理员 ======
$pushTitle = '新订阅 - amuchen.com';
$pushContent = '<div style="font-family:-apple-system,BlinkMacSystemFont,sans-serif;max-width:480px">'
             . '<h3 style="color:#7c3aed;margin:0 0 16px">新用户订阅通知</h3>'
             . '<table style="width:100%;border-collapse:collapse;font-size:14px">'
             . '<tr><td style="padding:8px 12px;background:#f8f7ff;border-radius:6px 0 0 0;color:#666;width:80px">邮箱</td>'
             . '<td style="padding:8px 12px;background:#f8f7ff;border-radius:0 6px 0 0"><strong>' . htmlspecialchars($email) . '</strong></td></tr>'
             . '<tr><td style="padding:8px 12px;color:#666">时间</td>'
             . '<td style="padding:8px 12px">' . date('Y-m-d H:i:s') . '</td></tr>'
             . '<tr><td style="padding:8px 12px;background:#f8f7ff;color:#666">IP</td>'
             . '<td style="padding:8px 12px;background:#f8f7ff">' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '</td></tr>'
             . '<tr><td style="padding:8px 12px;border-radius:0 0 0 6px;color:#666">总订阅</td>'
             . '<td style="padding:8px 12px;border-radius:0 0 6px 0"><strong style="color:#7c3aed">' . count($subscribers) . '</strong> 人</td></tr>'
             . '</table>'
             . '<p style="margin:16px 0 0;font-size:12px;color:#999">来自 amuchen.com 订阅系统</p>'
             . '</div>';
showdocPush($pushTitle, $pushContent);

// ====== 2. 给用户发送确认邮件（HTML 格式）======
$userSubject = '=?UTF-8?B?' . base64_encode('订阅成功 - 沐辰网络 amuchen.com') . '?=';
$userBody = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f4f3ff;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif">'
          . '<div style="max-width:520px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(124,58,237,0.08)">'
          . '<div style="background:linear-gradient(135deg,#7c3aed,#a78bfa);padding:40px 32px;text-align:center">'
          . '<h1 style="color:#fff;margin:0;font-size:22px;font-weight:600;letter-spacing:2px">AMUCHEN</h1>'
          . '<p style="color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:13px">沐辰网络</p>'
          . '</div>'
          . '<div style="padding:32px">'
          . '<h2 style="color:#1a1a2e;margin:0 0 8px;font-size:18px">订阅成功 ✓</h2>'
          . '<p style="color:#666;font-size:14px;line-height:1.7;margin:0 0 24px">感谢您订阅沐辰网络的上线通知。我们正在精心打造全新的网站体验，上线后会第一时间通知您。</p>'
          . '<div style="background:#f8f7ff;border-radius:10px;padding:16px 20px;margin:0 0 24px">'
          . '<table style="width:100%;border-collapse:collapse;font-size:13px">'
          . '<tr><td style="padding:6px 0;color:#999;width:60px">邮箱</td><td style="padding:6px 0;color:#333;font-weight:500">' . htmlspecialchars($email) . '</td></tr>'
          . '<tr><td style="padding:6px 0;color:#999">时间</td><td style="padding:6px 0;color:#333">' . date('Y-m-d H:i:s') . '</td></tr>'
          . '</table></div>'
          . '<p style="color:#999;font-size:12px;margin:0">如果这不是您本人操作，请忽略此邮件。</p>'
          . '</div>'
          . '<div style="border-top:1px solid #f0eeff;padding:20px 32px;text-align:center">'
          . '<p style="margin:0;font-size:12px;color:#aaa">沐辰网络 · <a href="https://amuchen.com" style="color:#7c3aed;text-decoration:none">amuchen.com</a> · <a href="mailto:hi@amuchen.com" style="color:#7c3aed;text-decoration:none">hi@amuchen.com</a></p>'
          . '</div></div></body></html>';

smtpSend($email, $userSubject, $userBody, true);

echo json_encode([
    'success' => true,
    'message' => '订阅成功',
    'isNew'   => true,
    'total'   => count($subscribers)
]);

// ====== SMTP 发信函数（原生 socket，无需第三方库）======
function smtpSend($to, $subject, $body, $isHtml = false) {
    $host = 'ssl://' . SMTP_HOST;
    $port = SMTP_PORT;

    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) return false;

    $response = '';
    $log = [];

    // 读取服务器响应
    $read = function() use ($socket, &$response) {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    };

    // 发送命令
    $send = function($cmd) use ($socket, $read, &$log) {
        fwrite($socket, $cmd . "\r\n");
        $resp = $read();
        $log[] = ['cmd' => trim($cmd), 'resp' => trim($resp)];
        return $resp;
    };

    try {
        $read(); // 欢迎信息

        $send('EHLO amuchen.com');

        // AUTH LOGIN
        $send('AUTH LOGIN');
        $send(base64_encode(SMTP_USER));
        $resp = $send(base64_encode(SMTP_PASS));

        if (strpos($resp, '235') === false) {
            fclose($socket);
            return false;
        }

        $send('MAIL FROM:<' . SMTP_FROM . '>');
        $send('RCPT TO:<' . $to . '>');
        $send('DATA');

        // 邮件内容
        $contentType = $isHtml ? 'text/html' : 'text/plain';
        $fromName = '=?UTF-8?B?' . base64_encode(SMTP_FROM_NAME) . '?=';
        $headers = "From: {$fromName} <" . SMTP_FROM . ">\r\n"
                 . "To: <{$to}>\r\n"
                 . "Subject: {$subject}\r\n"
                 . "MIME-Version: 1.0\r\n"
                 . "Content-Type: {$contentType}; charset=UTF-8\r\n"
                 . "Content-Transfer-Encoding: base64\r\n"
                 . "Date: " . date('r') . "\r\n"
                 . "Message-ID: <" . uniqid('amuchen_') . "@amuchen.com>\r\n"
                 . "\r\n"
                 . chunk_split(base64_encode($body));

        $resp = $send($headers . "\r\n.");

        $send('QUIT');
        fclose($socket);

        return strpos($resp, '250') !== false;
    } catch (\Exception $e) {
        @fclose($socket);
        return false;
    }
}

// ====== ShowDoc 微信推送函数 ======
function showdocPush($title, $content) {
    $postData = http_build_query([
        'title'   => $title,
        'content' => $content
    ]);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $postData,
            'timeout' => 10
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);

    $result = @file_get_contents(SHOWDOC_PUSH_URL, false, $ctx);
    if ($result) {
        $json = json_decode($result, true);
        return isset($json['error_code']) && $json['error_code'] === 0;
    }
    return false;
}