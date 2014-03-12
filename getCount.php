<?php
header('content-type:application/x-javascript');
//ini_set('display_errors', 'On'); //error_reporting(E_ALL | E_STRICT);

// Get the total number of victims in the database (includes deleted rows!)

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

$q = 'SELECT count(*) FROM victims';
$result = $link->query($q);
$answer = $result->fetch_row();
$json = json_encode($answer);
echo $_GET['callback'] . '(' . $json . ');';


?>