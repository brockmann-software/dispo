<?php
require_once 'Zend/config.php';
$config = Zend_Config::get(realpath(dirname(__FILE__) . '/../application/config/application.ini');
$path = $config->upload->quality->pictures;
header('Content-type: image/jpeg');
print(
?>