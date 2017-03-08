<?php

// Should be run once a hour

include 'config.php';

function is_changed_pair($ip_address, $mac_address, $vlan_tag) {
	global $sql, $DB;
	$sth = $sql->prepare('SELECT * FROM known_pairs WHERE ip_address = :ip_address AND vlan_tag = :vlan_tag');
	$sth->bindvalue(':ip_address', $ip_address, PDO::PARAM_STR);
	$sth->bindvalue(':vlan_tag', $vlan_tag, PDO::PARAM_STR);
	$sth->execute();
	
	$result = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row) {
		if($row['mac_address'] == $mac_address && $row['ip_address'] == $ip_address && $row['vlan_tag'] == $vlan_tag) continue;
		return 1;
	}
	return 0;
}

function is_new_pair($ip_address, $mac_address, $vlan_tag) {
	global $sql;
	$sth = $sql->prepare("SELECT COUNT(ip_address) as count FROM known_pairs WHERE ip_address = :ip_address AND vlan_tag = :vlan_tag");
	$sth->bindvalue(":ip_address", $ip_address);
	$sth->bindvalue(":vlan_tag", $vlan_tag);
	$sth->execute();
	
	if($sth==false) return 1;
	$row = $sth->fetch(PDO::FETCH_ASSOC);
	if($row['count']==0) return 1;
	return 0;
}

function notify_email($type, $pair) {
	global $sql, $DB, $CFG;
	if(isset($pair['ip_address'])) {
		$hostname = gethostbyaddr($pair['ip_address']);
		$output = <<<EOF
hostname:	$hostname
ip_address:	${pair['ip_address']}
interface:	${pair['interface']}
vlan_tag:	${pair['vlan_tag']}
mac_address:	${pair['mac_address']}
timestamp:	${pair['tstamp']}

History:
EOF;
	}
	if($type == 'new_pairs') {
		$output = "timestamp		ip_address	mac_address		vlan_tag	hostname\n";
		foreach($pair as $row) {
			if(isset($previous) && $previous['mac_address'] == $row['mac_address']) continue;
			$hostname = gethostbyaddr($row['ip_address']);
			$output .= "${row['tstamp']}	${row['ip_address']}	${row['mac_address']}	${row['vlan_tag']}		$hostname\n";
			$previous = $row;
		}
		$subject = "addrwatch: new ip-mac pairs";
	} elseif($type == 'changed') {
		$sth = $sql->prepare("SELECT * FROM ".$DB['table']." WHERE ip_address = :ip_address ORDER by tstamp DESC");
		$sth->bindvalue(":ip_address", $pair['ip_address']);
		$sth->execute();
		$output .= "timestamp		ip_address	mac_address		vlan_tag\n";
		foreach($sth->fetchAll(PDO::FETCH_ASSOC) as $row) {
			if(isset($previous) && $previous['mac_address'] == $row['mac_address']) continue;
			$output .= "${row['tstamp']}	${row['ip_address']}	${row['mac_address']}	${row['vlan_tag']}\n";
			$previous = $row;
		}
		$subject = "addrwatch: mac_address changed for ip_address ".$pair['ip_address']." (".$hostname.")";
	} else {
		$subject = "addrwatch: new ip-mac pair ".$pair['ip_address'];
	}
	if(!mail($CFG['mailto'], $subject, $output)) echo "cant send notify_email\n";
}

$checked_ips = '';
$new_pairs = '';
$result = $sql->query("SELECT * FROM ".$DB['table']." WHERE tstamp > NOW() - INTERVAL 1 HOUR ORDER by tstamp DESC");
foreach($result as $row) {
	if(in_array($row['mac_address'], $CFG['mac_ignore'])) continue;
	if(isset($checked_ips[$row['ip_address']])) continue;
	if(is_changed_pair($row['ip_address'], $row['mac_address'], $row['vlan_tag'])) {
		$sth = $sql->prepare('UPDATE known_pairs SET mac_address = :mac_address, changes = changes+1 WHERE vlan_tag = :vlan_tag AND ip_address = :ip_address');
		$sth->bindvalue(':ip_address', $row['ip_address'], PDO::PARAM_STR);
		$sth->bindvalue(':mac_address', $row['mac_address'], PDO::PARAM_STR);
		$sth->bindvalue(':vlan_tag', $row['vlan_tag'], PDO::PARAM_STR);
		$sth->execute();
		notify_email('changed', $row);
	}
	if(is_new_pair($row['ip_address'], $row['mac_address'], $row['vlan_tag'])) {
		$sth = $sql->prepare("INSERT INTO known_pairs (ip_address,mac_address,vlan_tag,last_change) VALUES (:ip_address,:mac_address,:vlan_tag,NOW())");
		$sth->bindvalue(':ip_address', $row['ip_address'], PDO::PARAM_STR);
		$sth->bindvalue(':mac_address', $row['mac_address'], PDO::PARAM_STR);
		$sth->bindvalue(':vlan_tag', $row['vlan_tag'], PDO::PARAM_STR);
		$sth->execute();
		//notify_email('new', $row);
		$new_pairs[]=$row;
	}
	$checked_ips[$row['ip_address']]=1;
}

if(!empty($new_pairs)) {
	notify_email('new_pairs', $new_pairs);
}
?>