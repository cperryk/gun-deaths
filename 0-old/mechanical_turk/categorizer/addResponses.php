<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

include('csvToJSON.php');
$data = csvToDictionary('');

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');


$link->close();


?>