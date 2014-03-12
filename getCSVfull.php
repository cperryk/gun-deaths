<?php
header('content-type:text/csv');
header("Content-Disposition: attachment; filename=SlateGunDeaths.csv");
header("Pragma: no-cache");
header("Expires: 0");
require('checkIfLoggedIn.php');
//ini_set('display_errors', 'On'); error_reporting(E_ALL | E_STRICT);

// Output the whole Gun Deaths database as CSV, including columns hidden to outside users

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

//a list of properties that the user can retrieve
$properties = array('victimID','date','name','gender','age','ageGroup','city','state','lat','lng','url','note','lastModified','added');
$properties_count = count($properties)-1;
$query = 'SELECT '.implode(',',$properties).' from victims where deleted=false';

function getOrderBy(){
    return ' ORDER BY date';
}

$query.=getOrderBy();
$rows = array();
echo join(',',$properties);
echo "\n";
$result = mysqli_query($link,$query);
while($r = mysqli_fetch_assoc($result)) { //for each victim
    $victim = array();
    $count=0;
    foreach(array_keys($r) as $prop){
        echo '"'.$r[$prop].'"';
        if($count<$properties_count){
            echo ',';
            $count++;
        }
    }
    echo "\n";
    //array_push($rows,$victim);
}
mysqli_close($link);


?>