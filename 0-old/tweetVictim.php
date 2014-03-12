<?php

header('content-type:application/x-javascript');
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

//connect to database
$link = mysqli_connect('quizdb.cvhpc2dxvogu.us-east-1.rds.amazonaws.com', 'quizuser', 'slat3Qu12','gunDeaths');
if (!$link) {die('Could not connect: ' . mysqli_error($link));}

$url = mysqli_real_escape_string($link,$_GET['url']);
$query = "Update victims set tweeted=true where url='$url'";
//echo $query;
mysqli_query($link,$query);
mysqli_close($link);

?>