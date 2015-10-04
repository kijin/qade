<?php

$config['basedir'] = dirname(__DIR__);
$config['detect_encodings'] = 'UTF-8';
$config['default_encoding'] = 'UTF-8';
$config['default_timezone'] = 'Etc/UTC';

$config['tab_size'] = 4;
$config['tab_type'] = 'space';
$config['tab_override_paths'] = array();
$config['tab_override_override_paths'] = array();

if (file_exists(__DIR__ . '/scratch/config.php'))
{
    include __DIR__ . '/scratch/config.php';
}
