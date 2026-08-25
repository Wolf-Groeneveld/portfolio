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

$ok = $valid && save_message($name, $email, $message);

header('Location: index.php?sent=' . ($ok ? '1' : '0') . '#contact');
exit;
