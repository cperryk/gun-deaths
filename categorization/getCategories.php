<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
$THRESHOLD = (2/3);
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php'); $link->select_db('gunDeaths');
$result = $link->query("SELECT * from cat_responses WHERE category!='broken'");
$victims = array();


//add up the categories of each victim
while($r = $result->fetch_assoc()){
	$id = $r['victimID'];
	$cat = $r['category'];
	if(!isset($victims[$id])){
		$victims[$id] = array();
	}
	if(!isset($victims[$id][$cat])){
		$victims[$id][$cat]=array('count'=>0);
	}
	$victims[$id][$cat]['count']++;
}

$victim_classifications = array();

//get the percents
foreach($victims as $key=>$victim){
	$total_votes = 0;
	foreach($victim as $cat_name=>$cat){
		$total_votes += $victim[$cat_name]['count'];
	}
	foreach($victim as $cat){
		$perc =  $victim[$cat_name]['count']/$total_votes;
		$victims[$key][$cat_name]['perc'] = $perc;
		if($perc >= $THRESHOLD){
			$victim_classifications[$key] = $cat_name;
		}
	}
}

//add up proportions
$cats = array();
$qualified_count = 0;
foreach($victim_classifications as $victimID=>$class){
	if(!isset($cats[$class])){
		$cats[$class]=array('count'=>0,'perc'=>0);
	}
	$cats[$class]['count']++;
	$qualified_count++;
}
echo 'Qualified victims: '.$qualified_count;
foreach($cats as $cat=>$arry){
	$cats[$cat]['perc'] = round(100*($arry['count']/$qualified_count));
}
var_dump($cats);
//var_dump($victim_classifications);


?>