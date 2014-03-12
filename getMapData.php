<?php
header('content-type:application/x-javascript');

// This script is used to provide data to the map on the front-end interactive. It returns the 1,000 cities with the most gun deaths.

require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');
$query = 'SELECT lat,lng,city,state,count(*) FROM victims GROUP BY lat,lng ORDER BY COUNT(*) DESC LIMIT 1000';
$result = mysqli_query($link,$query);

//process data
$rows = array();
while($r = mysqli_fetch_assoc($result)) {
    $victim = array();
    foreach(array_keys($r) as $prop){
        $victim[$prop] = $r[$prop];
    }
    array_push($rows,$victim);
}
mysqli_close($link);
$json = json_encode($rows);
echo $_GET['callback'] . '(' . $json . ');';

?>