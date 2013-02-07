<?php

$my_file = 'payload.json';
$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
$data = $_POST['payload'];
fwrite($handle, $data);

?>