<?php

$base = substr($_GET['p'], 0, -10);
$path = base64_decode($base);

$mime_type="application/octet-stream"; // modify accordingly to the file type of $_GET['path'], but in most cases no need to do so

if (file_exists($path)) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: " . $mime_type);
    header("Content-Length: " .(string)(filesize($path)) );
    header('Content-Disposition: attachment; filename="'.basename($path).'"');
    header("Content-Transfer-Encoding: binary\n");
    readfile($path); // outputs the content of the file
} else {
    die('File could not be found, is it deleted?');
}

exit();

?>