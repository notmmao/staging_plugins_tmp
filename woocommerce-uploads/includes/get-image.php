<?php

$base = substr($_GET['p'], 0, -10);
$path = base64_decode($base);

$mime_type="application/octet-stream"; // modify accordingly to the file type of $_GET['path'], but in most cases no need to do so

if (file_exists($path)) {
    header('Content-Type: image/jpeg');
    readfile($path);
} else {
    die('File could not be found, is it deleted?');
}

exit();

?>