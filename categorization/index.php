<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once($_SERVER['DOCUMENT_ROOT'].'/getIP.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php'); $link->select_db('gunDeaths');
$OPTIONS = array('murder','suicide','accident','police','defense','other','broken');
?>

<html>
<head>
	<title>Gun Deaths Categorizer</title>
	<link rel="stylesheet" type="text/css" href="categorizer_styles.css?version=2"/>
	<script type="text/javascript" src="jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="categorizer.js"></script>
</head>
<body>

<?
//a list of properties that the user can retrieve

$ip = $link->real_escape_string(getIP());

function addResponse(){
	global $link, $OPTIONS, $ip;
	if(isset($_POST)){
		foreach($OPTIONS as $option){
			if(isset($_POST[$option])){
				$updated_victim_id = $link->real_escape_string($_POST['victimID']);
				$option = $link->real_escape_string($option);
				$q = "INSERT INTO cat_responses(victimID,category,ip,time) VALUES($updated_victim_id,'$option','$ip',current_timestamp)";
				$result = $link->query($q);
				if($result){
					if($option=='broken'){
						$q = "UPDATE victims SET broken = 1, categorization_votes = categorization_votes + 1 WHERE victimID = $updated_victim_id";
					}
					else{
						$q = "UPDATE victims SET categorization_votes = categorization_votes+1 WHERE victimID = $updated_victim_id";
					}
					$link->query($q);
				}
				break;
			}
		}
	}
}
addResponse();

if(isset($_GET['victimID'])){
	$q = 'SELECT victimID,url,name,gender,age,ageGroup,city,state,`date` FROM victims WHERE victimID='.$link->real_escape_string($_GET['victimID']);
}
else{
	/*\$q = 'SELECT count(*) FROM victims WHERE deleted=0 AND broken=0';
	$result = $link->query($q);
	$ct = $result->fetch_row()[0];
	$rnd = rand(0,$ct);	
	$q = "SELECT victimID,url,name,gender,age,ageGroup,city,state,`date` FROM victims WHERE deleted=0 AND broken=0 LIMIT $rnd,1";*/
	//$q = 'SELECT victimID,url,name,gender,age,ageGroup,city,state,`date` FROM victims WHERE deleted=0 AND broken=0 ORDER BY rand(),categorization_votes DESC LIMIT 1';
	$q = 'SELECT victimID,url,name,gender,age,ageGroup,city,state,`date` FROM victims WHERE deleted=0 ORDER BY categorization_votes,rand() LIMIT 1';
	//$q = 'SELECT victimID,url,name,gender,age,ageGroup,city,state,`date` FROM victims WHERE deleted=0 AND broken=0 ORDER BY categorization_votes LIMIT 1';
}
//echo $q;

$result = $link->query($q);
$link->close();
$assoc = $result->fetch_assoc();
$victim_id = $assoc['victimID'];
$url = $assoc['url'];

function getQuestion($name,$gender,$age,$city,$state,$date){
	$s = 'What best describes how ';
	if($name!==''){
		$s.='<strong>'.$name.'</strong> died in this article?';
		return $s;
	}

	$s.= 'the <strong>';
	if($age!==null){
		$s.=$age.'-year-old ';
	}
	if($gender!==''){
		if($gender=='M'){
			if($age!==null){
				if($age>17){
					$s.='man';
				}
				else{
					$s.='boy';
				}
			}
			else{
				$s.='man';
			}
		}
		else if($gender=='F'){
			if($age!==null){
				if($age>17){
					$s.='woman';
				}
				else{
					$s.='boy';
				}
			}
			else{
				$s.='woman';
			}
		}
	}
	else{
		$s.='person';
	}
	$s.='</strong>';
	if($city!==''&&$city!==null){
		$s.=' in <strong>'.$city;
		if($state!==''&&$state!==null){
			$s.=', '.$state;
		}
		$s.='</strong>';
	}
	function YYYYMMDDtoMMDDYYYY($dateString){
		$months = array(
			1=>'Jan.',
			2=>'Feb.',
			3=>'March',
			4=>'April',
			5=>'May',
			6=>'June',
			7=>'July',
			8=>'Aug.',
			9=>'Sep.',
			10=>'Oct.',
			11=>'Nov.',
			12=>'Dec.'
		);
	    $value = explode('-',$dateString);
	    $year = $value[0];
	    $month = $value[1];
	    if($month[0]==='0'){
	    	$month = $month[1];
	    }
	    $day = $value[2];
	    if($day[0]==='0'){
	    	$day = $day[1];
	    }
	    return $months[$month].' '.$day;
	}
	if($date!==''&&$date!==null){
		$s.=' shot on <strong>'.YYYYMMDDtoMMDDYYYY($date).'</strong>';
	}
	$s.=' died?';
	return $s;
}
$question = getQuestion($assoc['name'],$assoc['gender'],$assoc['age'],$assoc['city'],$assoc['state'],$assoc['date']);
?>
<div class="wrapper">
<div class="lower">
	<a id="article_new_window" target="_blank" href="<?=$url?>">Open article in new window &#187;</a>
	<div id="pane1">
		<p class="instructions"><?=$question?></p>
		<form action="index.php" method="post">
			<div class="options">
			 	<input type="hidden" name="victimID" value="<?=$victim_id?>" />
				<input type="submit" class="option" name="murder" value="Murder"/>
				<input type="submit" class="option" name="suicide" value="Suicide"/>
				<input type="submit" class="option" name="accident" value="Accident"/>
				<input type="submit" class="option" name="police" value="Shot by Law Enforcement"/>
				<input type="submit" class="option" name="defense" value="Shot by Civilian in Self-Defense"/>
				<input type="submit" class="option" name="other" value="Other / Unclear"/>
				<input type="submit" class="option special_btn" name="broken" value="This article is broken"/>
			</div>
		</form>
	</div>
</div>
<center>
<iframe src="<?=$url?>" frameborder="0" sandbox=""></iframe>
</center>
</div>
</body>
</html>

<?
?>