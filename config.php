<?php 

$DB['host'] = 'localhost';
$DB['user'] = 'addrwatch';
$DB['pass'] = 'addrwatch';
$DB['name'] = 'addrwatch';
$DB['table'] = 'addrwatch';

$CFG['mac_ignore'] = array('5c:71:0d:e3:a4:3f');
$CFG['mailto'] = 'admin@iphost.md';

ini_set('display_errors', 'On');
ini_set('short_open_tag', 'On');
error_reporting(E_ALL);

$sql = new PDO("mysql:host=".$DB['host'].";port=3306;dbname=".$DB['name'].";charset=UTF8;", $DB['user'], $DB['pass'], array(PDO::ATTR_PERSISTENT=>true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$sql->query("SET NAMES utf8;");

?>