<?php
header('content-type:application/x-javascript');
//ini_set('display_errors', 'On'); error_reporting(E_ALL | E_STRICT);

// Return information about the revision history of a victim.

require('checkIfLoggedIn.php');

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

$victimID = mysqli_real_escape_string($link,$_GET['victimID']);
$query = "SELECT user,time,property,fromValue,toValue FROM changeLog WHERE victimID=$victimID";
$result = mysqli_query($link,$query);
mysqli_close($link);
$changes = array();
while ($row = $result->fetch_assoc()){
    array_push($changes,$row);
}
$json = json_encode($changes);
echo $_GET['callback'] . '(' . $json . ');';


?>