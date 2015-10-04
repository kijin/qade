<?php

$config['basedir'] = dirname(__DIR__);
$config['detect_encodings'] = 'UTF-8';
$config['default_encoding'] = 'UTF-8';
$config['default_timezone'] = 'Etc/UTC';

if (file_exists(__DIR__ . '/scratch/config.php'))
{
    include __DIR__ . '/scratch/config.php';
}
