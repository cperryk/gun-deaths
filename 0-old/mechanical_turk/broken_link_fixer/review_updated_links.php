<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);


include('csvToJSON.php');
$data = csvToDictionary('https://docs.google.com/spreadsheet/pub?key=0AmjG42aUKrlodDJaTWlGNk5lZzQxWVFvR3hpYnExQWc&output=csv');

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

$victims = array();

$result = $link->query('SELECT victimID, url FROM victims');
while($r = $result->fetch_assoc()){
	$victims[$r['victimID']] = array('new_link'=>$r['url']);
}
$result = $link->query('SELECT victimID, url FROM victims_save');
while($r = $result->fetch_assoc()){
	$victims[$r['victimID']]['old_link']=$r['url'];
}
$changed_victims = array();

$count = 0;
if(isset($_GET['csv'])){
	echo 'victimID,old url,new url<br/>';
}
foreach($victims as $victimID=>$victim){
	$new_url = $victim['new_link'];
	if(isset($victim['old_link'])){
		$old_url = $victim['old_link'];
		if($new_url!=$old_url){
			if(isset($_GET['csv'])){
				echo '"'.$victimID.'","'.$old_url.'","'.$new_url.'"<br/>';
			}
			else{
				echo "<strong>".$victimID."</strong>: <a href=\"$old_url\" target=\"_blank\">".$old_url."</a> -> <a href=\"$new_url\" target=\"blank\">".$new_url.'</a><br/><br/>';
			}
			$changed_victims[$victimID] = $victim;
			$count++;
		}
	}
}
if(!isset($_GET['csv'])){
	echo 'Total changed: '.$count;
}
?>