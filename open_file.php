<?php

include 'common.php';
if (!isset($_GET['token']) || !isset($_COOKIE['token']) || $_GET['token'] !== $_COOKIE['token'])
{
    echo 'CSRF'; exit;
}

header('Content-Type: application/json; charset=UTF-8');

$filename = isset($_GET['file']) ? trim($_GET['file'], '/') : '';
$filename = str_replace('..', '', $filename);
$filename = $config['basedir'] . '/' . $filename;

echo json_encode(get_file_info($filename), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
