<?php

include 'config.php';

//convert mac_address from cisco style mac address
if(preg_match("/^[a-f0-9.]+$/", $_GET['mac_address'])) {
	$_GET['mac_address'] = implode(":", str_split(str_replace(".", "", $_GET['mac_address']), 2));
}

function get_network_24($ip_address) {
	$i = explode('.', $ip_address);
	$i[3]=0;
	return implode('.', $i).'/24';
}

function sort_subnet($a, $b) {
	if ($a['ip_address_bin'] == $b['ip_address_bin']) return 0;
	return ($a['ip_address_bin'] < $b['ip_address_bin']) ? -1 : 1;
}

if(!empty($_GET['mac_address']) || !empty($_GET['ip_address'])) {
	$sth = $sql->prepare("SELECT * FROM ".$DB['table']." WHERE mac_address LIKE :mac_address AND ip_address LIKE :ip_address ORDER by tstamp DESC");
	$sth->bindvalue(':mac_address', '%'.$_GET['mac_address'].'%', PDO::PARAM_STR);
	$sth->bindvalue(':ip_address', '%'.$_GET['ip_address'].'%', PDO::PARAM_STR);
	$sth->execute();
	$result = $sth->fetchAll();
} elseif(!empty($_GET['subnet'])) {
	$subnet1 = explode('.', $_GET['subnet']);
	unset($subnet1[3]);
	$subnet = implode('.', $subnet1);
	
	$sth = $sql->prepare("SELECT * FROM known_pairs WHERE ip_address LIKE :ip_address ORDER by ip_address ASC");
	$sth->bindvalue(':ip_address', $subnet.'%', PDO::PARAM_STR);
	$sth->execute();
	$result = $sth->fetchAll();
	foreach($result as $i => $j) $result[$i]['ip_address_bin'] = ip2long($j['ip_address']);
	usort($result, 'sort_subnet');

	
	$echo_type = 'subnet';
} else {
	$result = $sql->query("SELECT * FROM ".$DB['table']." ORDER by tstamp DESC LIMIT 100");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>addrwatch Web interface</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
<body>
    <div class="container" style="padding-top: 30px;">
	<div class="row">
	<form class="form-inline">
	  <div class="form-group">
	    <label for="ip_address">IP Address:  </label>
	    <input type="text" class="form-control" id="ip_address" name="ip_address" value="<?=$_GET['ip_address']?>" ="1.1.1.1">
	  </div>
	  <div class="form-group">
	    <label for="mac_address">MAC Address:  </label>
	    <input type="text" class="form-control" id="mac_address" name="mac_address" value="<?=$_GET['mac_address']?>" placeholder="00:1c:2b:3a:40:59">
	  </div>
	  <button type="submit" class="btn btn-default">Search</button>
	</form>
	</div>
	<div class="row" style="padding-top: 30px;">
	<table class="table table-bordered table-condensed table-hover">
	<?php if($echo_type == 'subnet') { ?>
	  <thead>
	    <th>Last change</th>
	    <th>IP address</th>
	    <th>Mac address</th>
	    <th>Vlan</th>
	    <th>Changes</th>
	  </thead>
	<?php } else { ?>
	  <thead>
	    <th>Timestamp</th>
	    <th>IP address</th>
	    <th>Mac address</th>
	    <th>Interface</th>
	    <th>Vlan</th>
	    <th>Type</th>
	  </thead>
	<?php } ?>
	  <tbody>
<?php

if($echo_type == 'subnet') {
	foreach($result as $row) {
		$subnet = get_network_24($row['ip_address']);
		echo "<tr><td>".$row['last_change']."</td><td>".$row['ip_address']."</td><td>".$row['mac_address']."</td><td>".$row['vlan_tag']."</td><td>".$row['changes']."</td></tr>";
	}
} else {
	foreach($result as $row) {
		$subnet = get_network_24($row['ip_address']);
		echo "<tr><td>".$row['tstamp']."</td><td>".$row['ip_address']." <a href='?subnet=".$subnet."'>/24</a></td><td>".$row['mac_address']."</td><td>".$row['interface']."</td><td>".$row['vlan_tag']."</td><td>".$row['origin']."</td></tr>";
	}
}
	  ?>
	   </tbody>
	</table>
	</div>
    </div>
</div>
</body>
</html>