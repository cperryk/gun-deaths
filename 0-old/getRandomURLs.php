<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

for($i=0;$i<1000;$i++){
	$r = rand(0,1);
	if($r<0.1){
		echo 'http://slate-interactive3-prod.elasticbeanstalk.com/gun-deaths/categorization/';
	}
	else{
		$q = 'SELECT victimID,url,name,gender,age,ageGroup,city,state,`date` FROM victims WHERE deleted=0 AND broken=0 ORDER BY rand(),categorization_votes DESC LIMIT 1';
		$result = $link->query($q)->fetch_assoc();
		$victimID = $result['victimID'];
		$options = array('murder','suicide','accident','police','defense','other','broken');
		$input = $options[array_rand($options)];
		echo "http://slate-interactive3-prod.elasticbeanstalk.com/gun-deaths/categorization/index.php?victimID=$victimID&$input=$input";
	}
	echo '<br/>';

}

?>