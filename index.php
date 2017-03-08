<?php

$DB['user'] = 'addrwatch';
$DB['pass'] = 'addrwatch';
$DB['name'] = 'addrwatch';
$DB['table'] = 'addrwatch';

$sql = new PDO("mysql:host=127.0.0.1;port=3306;dbname=".$DB['name'].";charset=UTF8;", $DB['user'], $DB['pass'], array(PDO::ATTR_PERSISTENT=>true));
$sql->query("SET NAMES utf8;");

ini_set('display_errors', 'On');
ini_set('short_open_tag', 'On');
error_reporting(-1);

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
	  <thead>
	    <th>Timestamp</th>
	    <th>Interface</th>
	    <th>Vlan</th>
	    <th>IP address</th>
	    <th>Mac address</th>
	    <th>Type</th>
	  </thead>
	  <tbody>
<?php
if(!empty($_GET['mac_address']) || !empty($_GET['ip_address'])) {
	$sth = $sql->prepare("SELECT * FROM ".$DB['table']." WHERE mac_address LIKE :mac_address AND ip_address LIKE :ip_address ORDER by tstamp DESC");
	$sth->bindvalue(':mac_address', '%'.$_GET['mac_address'].'%', PDO::PARAM_STR);
	$sth->bindvalue(':ip_address', '%'.$_GET['ip_address'].'%', PDO::PARAM_STR);
	$sth->execute();
	$result = $sth->fetchAll();
} else {
	$result = $sql->query("SELECT * FROM ".$DB['table']." ORDER by tstamp DESC LIMIT 100");
}
foreach($result as $row) {
	echo "<tr><td>".$row['tstamp']."</td><td>".$row['interface']."</td><td>".$row['vlan_tag']."</td><td>".$row['ip_address']."</td><td>".$row['mac_address']."</td><td>".$row['origin']."</td></tr>";
}
	  ?>
	   </tbody>
	</table>
	</div>
    </div>
</div>
</body>
</html>