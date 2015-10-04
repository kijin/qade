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
else
{
    $cmd = preg_replace('#^ls(?:\s|$)#', 'ls --group-directories-first ', $cmd);
    $output = @shell_exec('TZ=' . $config['default_timezone'] . ' ' . $cmd . ' 2>&1');
    $dir = getcwd();
}

echo 'OK' . "\n" . $dir . "\n" . preg_replace('/\r?\n/', '', nl2br(escape($output)));
exit;
