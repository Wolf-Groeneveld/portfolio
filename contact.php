<?php
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (trim($_POST['website'] ?? '') !== '') {
    header('Location: index.php?sent=1#contact');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

$valid = $name !== ''
    && $message !== ''
    && filter_var($email, FILTER_VALIDATE_EMAIL)
    && mb_strlen($name) <= 120
    && mb_strlen($email) <= 190
    && mb_strlen($message) <= 5000;

$ok = false;
if ($valid) {
    $saved = save_message($name, $email, $message);
    $sent = send_contact_mail($name, $email, $message);
    $ok = $sent || $saved;
}

header('Location: index.php?sent=' . ($ok ? '1' : '0') . '#contact');
exit;

function send_contact_mail(string $name, string $fromEmail, string $message): bool
{
    $to = SITE_EMAIL;
    $subject = 'Portfolio contact van ' . $name;
    $body = "Naam: {$name}\nE-mail: {$fromEmail}\n\n{$message}\n";
    $headers = implode("\r\n", [
        'From: ' . $to,
        'Reply-To: ' . $fromEmail,
        'Content-Type: text/plain; charset=UTF-8',
    ]);
    $mailed = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);

    $payload = json_encode([
        'name' => $name,
        'email' => $fromEmail,
        'message' => $message,
        '_subject' => $subject,
        '_replyto' => $fromEmail,
    ], JSON_UNESCAPED_UNICODE);

    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $payload,
            'timeout' => 12,
            'ignore_errors' => true,
        ],
    ]);
    $res = @file_get_contents('https://formsubmit.co/ajax/' . rawurlencode($to), false, $ctx);
    $forwarded = false;
    if (is_string($res)) {
        $json = json_decode($res, true);
        $forwarded = !empty($json['success']);
    }

    return $mailed || $forwarded;
}
