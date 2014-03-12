<?php
header('content-type:application/x-javascript');
require('checkIfLoggedIn.php');
//ini_set('display_errors', 'On'); error_reporting(E_ALL | E_STRICT);

// Delete a victim from the database.

// Connect to the victims database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php'); // A script that connects to Slate's interactives database
$link->select_db('gunDeaths');

$userID = $link->real_escape_string($_SESSION['userid']);
$victimID = $link->real_escape_string($_GET['victimID']);
$query = "UPDATE victims SET deleted=1, userID='$userID' WHERE victimID=$victimID";
$result = $link->query($query);
$link->close();
echo $_GET['callback'] . '();';

?>