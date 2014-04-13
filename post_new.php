<?php

include 'common.php';
if (!isset($_POST['token']) || !isset($_COOKIE['token']) || $_POST['token'] !== $_COOKIE['token'])
{
    echo 'CSRF'; exit;
}

header('Content-Type: application/json; charset=UTF-8');

$dir = trim($_POST['dir'], '/');
$dir = str_replace('..', '', $dir);

$filename = trim($_POST['filename'], '/');
$filename = str_replace('..', '', $filename);
if ($filename === '')
{
    echo json_encode(array('error' => 'Please enter the filename.'));
    exit;
}

$new_file_path = $config['basedir'] . '/' . $dir . '/' . $filename;
$new_file_path = str_replace('//', '/', $new_file_path);

if (!file_exists($new_file_path))
{
    @file_put_contents($new_file_path, '');
}

echo json_encode(get_file_info($new_file_path));
