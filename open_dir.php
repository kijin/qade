<?php

include 'common.php';
if (!isset($_GET['token']) || !isset($_COOKIE['token']) || $_GET['token'] !== $_COOKIE['token'])
{
    echo 'CSRF'; exit;
}

header('Content-Type: application/json; charset=UTF-8');

$basedir = isset($_GET['dir']) ? trim($_GET['dir'], '/') : '';
if ($basedir === '.') $basedir = '';
$basedir = str_replace('..', '', $basedir);
$basedir_full = $config['basedir'] . '/' . $basedir;

list($dirs, $files) = list_dir($basedir_full);
$dirs = array_reverse($dirs);
$files = array_reverse($files);

echo json_encode(array('dirs' => $dirs, 'files' => $files));
