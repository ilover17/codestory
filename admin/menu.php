<?php
require_once 'config.php';
//$json=${$_GET['type']};
$json=$tabs;
echo json_encode($json);