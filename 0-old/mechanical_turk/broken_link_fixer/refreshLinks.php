<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

include('csvToJSON.php');
$data = csvToDictionary('https://docs.google.com/spreadsheet/pub?key=0AmjG42aUKrlodDJaTWlGNk5lZzQxWVFvR3hpYnExQWc&output=csv');

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

foreach($data as $victim){
    $victimID = $victim['victimID'];
    $new_url = $victim['url'];
    $q = "UPDATE victims SET url='$new_url',broken=0 WHERE victimID=$victimID";
    echo $q.'<br/>';
    $result = $link->query($q);
    echo $result;
}

$link->close();


?>