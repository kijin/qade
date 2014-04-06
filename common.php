<?php

include 'config.php';
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

function escape($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function list_dir($dir)
{
    $dir = rtrim($dir, '/');
    if (!file_exists($dir)) return array(array(), array());
    if (!is_dir($dir)) return array(array(), array());
    
    $dirs = array();
    $files = array();
    
    $scan = scandir($dir);
    foreach ($scan as $sc)
    {
        if ($sc === '.' || $sc === '..') continue; 
        $filename = $dir . '/' . $sc;
        if (is_dir($filename))
        {
            $dirs[] = $sc;
        }
        else
        {
            $files[] = $sc;
        }
    }

    return [$dirs, $files];
}

function get_file_info($filename)
{
    static $extension_map = array(
        'phps' => 'php',
        'js' => 'javascript',
        'pl' => 'perl',
        'py' => 'python',
        'rb' => 'ruby',
        'cs' => 'csharp',
    );
    
    static $binary_extensions = array(
        'doc', 'dot', 'docx', 'dotx', 'xls', 'xlsx', 'ppt', 'pptx', 'hwp', 'pdf',
        'odt', 'ods', 'odp', 'odb', 'odg', 'mdb', 'accdb', 'rtf', 'ttf', 'otf', 'eot', 'woff',
        'zip', 'rar', 'lzma', 'xul', 'bz2', 'gz', 'xz', 'tgz', 'tbz', 'tbz2', 'tar', 'deb', 'rpm', 'so', 'dll', 'exe',
        'bmp', 'gif', 'ico', 'jpeg', 'jpg', 'png', 'svg', 'tif', 'tiff', 'psd', 'ai', 'eps',
        'mp2', 'mp3', 'mp4', 'mid', 'midi', 'aif', 'aiff', 'ra', 'wav', 'ogg', 'flac',
        'avi', 'mkv', 'mpg', 'mpeg', 'qt', 'mov', 'flv', 'swf', 'wma', 'wmv',
    );
        
    $extension = ($pos = strrpos($filename, '.')) !== false ? strtolower(substr($filename, $pos + 1)) : 'text';
    if (array_key_exists($extension, $extension_map)) $extension = $extension_map[$extension];
    if (in_array($extension, $binary_extensions)) return array('error' => 'Not a text file.');
    if (!file_exists(__DIR__ . '/assets/ace-builds/src-min-noconflict/mode-' . $extension . '.js')) $extension = 'text';
    
    if (filesize($filename) > 1048576) return array('error' => 'File is too big.');
    $content = file_get_contents($filename);
    $encoding = mb_detect_encoding($content, $GLOBALS['config']['detect_encodings']);
    if ($encoding !== false && $encoding !== 'UTF-8') $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        
    $relative_path = substr($filename, strlen($GLOBALS['config']['basedir']) + 1);
    
    return array(
        'editorid' => sha1($relative_path),
        'filename' => $relative_path,
        'basename' => basename($filename),
        'extension' => $extension,
        'encoding' => ($encoding === false) ? 'UTF-8' : $encoding,
        'content' => $content,
    );
}
