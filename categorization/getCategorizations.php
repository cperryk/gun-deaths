<?php
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');

$victim_id = $link->real_escape_string($_GET['victimID']);
$q = "SELECT category,count(*) FROM cat_responses WHERE victimID = $victim_id GROUP BY category";
$result = $link->query($q);
$out = array();
while($assoc = $result->fetch_assoc()){
	$out[$assoc['category']]=$assoc['count(*)'];
}
echo $_GET['callback'].'('.json_encode($out).')';
?>