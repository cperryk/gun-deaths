<?php
header('content-type:text/csv');
header("Content-Disposition: attachment; filename=SlateGunDeaths.csv");
header("Pragma: no-cache");
header("Expires: 0");

ini_set('display_errors', 'On');
error_reporting(E_ALL);
$THRESHOLD = (2/3);
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php'); $link->select_db('gunDeaths');
$result = $link->query("SELECT * from cat_responses WHERE category!='broken'");
$victims = array();

//add up the categories of each victim

echo "victimID,categorization,\n";
while($r = $result->fetch_assoc()){
	$id = $r['victimID'];
	$cat = $r['category'];
	echo $id.','.$cat."\n";
}


?>