<?php
header('content-type:application/x-javascript');

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

//a list of properties that the user can retrieve
$query = 'SELECT date,gender,ageGroup,city,state,lat,lng,victimID,name,url FROM victims WHERE deleted=0 ORDER BY isNewtown, date DESC, ageGroup DESC, gender';
$result = mysqli_query($link,$query);
mysqli_close($link);
$rows = array();
while($r = mysqli_fetch_assoc($result)) {
    $victim = array();
    foreach(array_keys($r) as $prop){
        $victim[$prop] = $r[$prop];
    }
    array_push($rows,$victim);
}
$json = json_encode($rows);
echo $_GET['callback'] . '(' . $json . ');';
?>