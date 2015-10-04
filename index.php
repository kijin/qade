<?php

/**
* QADE : Quick and Dirty Editor
*
* URL: http://github.com/kijin/qade
* Version: 0.1.2
*
* Copyright (c) 2014 Kijin Sung <kijin@kijinsung.com>
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/

include 'common.php';

// Load directories and files from the base dir.

list($default_dirs, $default_files) = list_dir($config['basedir']);

// Load previous state.

$state = file_exists(__DIR__ . '/scratch/state.php') ? include(__DIR__ . '/scratch/state.php') : array();
$console_dir = (isset($state['console_dir']) && file_exists($state['console_dir']) && is_dir($state['console_dir'])) ? $state['console_dir'] : $config['basedir'];
$open_dirs = isset($state['open_dirs']) ? $state['open_dirs'] : array();
$open_files = isset($state['open_files']) ? $state['open_files'] : array();
$selected = isset($state['selected']) ? $state['selected'] : '';
$selected_exists = false;

// Check if previously open and selected files are still valid.

foreach ($open_files as $key => $file)
{
    if (file_exists($config['basedir'] . '/' . $file))
    {
        if (sha1($file) === $selected) $selected_exists = true;
    }
    else
    {
        unset($open_files[$key]);
        continue;
    }
}

// Create the base URL.

if (!strncmp($config['basedir'], $_SERVER['DOCUMENT_ROOT'], strlen($config['basedir'])))
{
    $baseurl = rtrim(substr($_SERVER['DOCUMENT_ROOT'], strlen($config['basedir'])), '/');
}
else
{
    $baseurl = '/';
}

// Create a cookie and assign a token to prevent CSRF attacks.

$token = sha1(openssl_random_pseudo_bytes(20));
setcookie('token', $token, 0, null, null, true, true);

// End of processing. Everything below is template code.

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>QADE</title>
    <link rel="stylesheet" type="text/css" media="all" href="https://cdn.jsdelivr.net/normalize/3.0.3/normalize.min.css" />
    <link rel="stylesheet" type="text/css" media="all" href="https://cdn.jsdelivr.net/fontawesome/4.4.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" media="all" href="assets/editor.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery.ui/1.11.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/ace/1.2.0/noconflict/ace.js"></script>
    <script type="text/javascript" src="assets/editor.js"></script>
</head>
<body data-basedir="<?php echo escape($config['basedir']); ?>" data-baseurl="<?php echo escape($baseurl); ?>" data-token="<?php echo $token; ?>">

<!-- Header -->

<header>
    
    <div id="buttons">
        <a id="new" href="javascript:void(0)"><i class="fa fa-file-o"></i> New</a>
        <a id="save" href="javascript:void(0)"><i class="fa fa-floppy-o"></i> Save</a>
        <?php $encodings = mb_list_encodings(); sort($encodings); ?>
        <select id="encoding">
            <option value=""></option>
            <?php foreach ($encodings as $encoding): ?>
                <?php $is_selected = ($encoding === $config['default_encoding']); ?>
                <option value="<?php echo escape($encoding); ?>" <?php if ($selected_exists && $is_selected): ?>selected=selected<?php endif; ?>><?php echo escape($encoding); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div id="tabs">
        <div id="tab_console" class="tab console <?php if (!$selected_exists): ?>selected<?php endif; ?>" data-filename="~">
            <a class="tab_link" href="javascript:void(0)">Console</a>
        </div>
    </div>
    
</header>

<!-- Content Area Begin -->

<div id="content_area">

<!-- Sidebar -->

<div id="sidebar">
    
    <?php foreach ($default_dirs as $dir): ?>
        <div class="dirtree" data-path="<?php echo escape($dir); ?>" style="margin-left:0">
            <a class="dir" href="javascript:void(0)"><i class="fa fa-folder"></i> <?php echo escape($dir); ?></a>
        </div>
    <?php endforeach; ?>
    
    <?php foreach ($default_files as $file): ?>
        <div class="dirtree" data-path="<?php echo escape($file); ?>" style="margin-left:0">
            <a class="file" href="javascript:void(0)"><i class="fa fa-file-text-o"></i> <?php echo escape($file); ?></a>
            <a class="direct" href="<?php echo escape($baseurl . '/' . $file); ?>" target="_blank"><i class="fa fa-caret-right"></i></a>
        </div>
    <?php endforeach; ?>
    
</div>

<!-- Editor Area -->

<div id="editors">

    <!-- Console -->
    
    <div id="instance_console" class="instance console" style="<?php if ($selected_exists): ?>display:none<?php endif; ?>">
        <div id="console_output" data-dir="<?php echo escape($console_dir); ?>"
            data-username="<?php echo escape(get_current_user()); ?>" data-hostname="localhost">
            <div class="item placeholder" style="color:#00a;font-weight:bold;margin:8px 0"><?php echo escape(get_current_user()); ?>@localhost:<?php echo escape($console_dir); ?>$</div>
        </div>
        <div id="console_cmd_container">
            <input type="text" id="console_cmd" style="border: 1px solid #ccc; padding: 4px" size="80" value="" />
        </div>
    </div>
    
</div>

<!-- Content Area End -->

</div>

<!-- Dialog Boxes -->

<form id="new_file_dialog" title="New File" class="dialog">
    <fieldset>
        <label for="name">Directory</label><br />
        <input type="text" id="new_file_dir" name="new_file_dir" value="" /><br />
        <label for="email">Filename</label><br />
        <input type="text" id="new_file_filename" name="new_file_filename" value="" /><br />
    </fieldset>
</form>

<?php
    $open_files_json = array();
    foreach ($open_files as $open_file)
    {
        $open_files_json[] = get_file_info($config['basedir'] . '/' . $open_file);
    }
?>

<script type="text/javascript">
    var open_dirs = <?php echo json_encode($open_dirs, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    var open_files = <?php echo json_encode($open_files_json, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    var selected_tab = <?php echo json_encode($selected, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
</script>

</body>
</html>
