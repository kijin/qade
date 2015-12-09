<?php

include 'common.php';
if (!isset($_POST['token']) || !isset($_COOKIE['token']) || $_POST['token'] !== $_COOKIE['token'])
{
    echo 'CSRF'; exit;
}

$dir = isset($_POST['dir']) ? $_POST['dir'] : $config['basedir'];
$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : '';

@chdir($dir);
if (preg_match('#^(?:cd|chdir)\s+(.+)$#', $cmd, $matches))
{
    $matches[1] = trim($matches[1], '\'"');
    if (@chdir($dir . '/' . $matches[1]))
    {
        $output = 'Working directory changed';
        $dir = getcwd();
    }
    else
    {
        $output = 'Error';
        $dir = getcwd();
    }
}
elseif (preg_match('/^<\?(=|php)?/', $cmd, $matches))
{
    date_default_timezone_set($config['default_timezone']);
    ini_set('html_errors', false);
    ob_start();
    eval(((isset($matches[1]) && $matches[1] === '=') ? 'echo ' : '') . trim(substr($cmd, strlen($matches[0]))) . ';');
    $output = ob_get_clean();
    $dir = getcwd();
}
else
{
    $cmd = preg_replace('#^ls(?:\s|$)#', 'ls --group-directories-first ', $cmd);
    $output = @shell_exec('TZ=' . $config['default_timezone'] . ' ' . $cmd . ' 2>&1');
    $dir = getcwd();
}

echo 'OK' . "\n" . $dir . "\n" . preg_replace('/\r?\n/', '', nl2br(escape($output)));
exit;
