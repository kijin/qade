<?php

include 'common.php';
if (!isset($_POST['token']) || !isset($_COOKIE['token']) || $_POST['token'] !== $_COOKIE['token'])
{
    echo 'CSRF'; exit;
}

$file = isset($_POST['file']) ? trim($_POST['file'], '/') : '';
$file = str_replace('..', '', $file);
$file_full = $config['basedir'] . '/' . $file;

$content = isset($_POST['content']) ? $_POST['content'] : '';
$encoding = isset($_POST['encoding']) ? $_POST['encoding'] : '';
$encodings = mb_list_encodings();
if (!in_array($encoding, $encodings)) $encoding = $config['default_encoding'];
if ($encoding !== 'UTF-8') $content = mb_convert_encoding($content, $encoding, 'UTF-8');
$content = str_replace("\r\n", "\n", $content);
if (!preg_match('/\n$/', $content)) $content .= "\n";

file_put_contents($file_full, $content);

echo 'OK';
exit;
