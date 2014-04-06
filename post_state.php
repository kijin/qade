<?php

include 'common.php';
if (!isset($_POST['token']) || !isset($_COOKIE['token']) || $_POST['token'] !== $_COOKIE['token'])
{
    echo 'CSRF'; exit;
}

unset($_POST['token']);
file_put_contents(__DIR__ . '/state.php', '<' . "?php\n\nreturn " . var_export($_POST, true) . ";\n");

echo 'OK';
exit;
