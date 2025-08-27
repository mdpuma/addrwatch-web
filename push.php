<?php

include 'config.php';

$args = ['hostname','interface','vlan_tag','mac_address','ip_address'];
foreach($args as $arg) {
    if(!isset($_GET[$arg])) {
        $_GET[$arg]='';
    }
}

if(empty($_GET['mac_address'])) {
    exit;
}

if(in_array($_GET['mac_address'], $CFG['mac_ignore'])) {
    exit;
}

$sth = $sql->prepare("INSERT INTO addrwatch (hostname,interface,vlan_tag,mac_address,ip_address) VALUES (:hostname, :interface, :vlan_tag, :mac_address, :ip_address)");
$sth->bindvalue(':hostname', $_GET['hostname'], PDO::PARAM_STR);
$sth->bindvalue(':interface', $_GET['interface'], PDO::PARAM_STR);
$sth->bindvalue(':vlan_tag', $_GET['vlan_tag'], PDO::PARAM_STR);
$sth->bindvalue(':mac_address', $_GET['mac_address'], PDO::PARAM_STR);
$sth->bindvalue(':ip_address', $_GET['ip_address'], PDO::PARAM_STR);
$sth->execute();

?>
