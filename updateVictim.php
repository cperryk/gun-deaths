<?php
header('content-type:application/x-javascript');
require('checkIfLoggedIn.php');
//ini_set('display_errors', 'On'); error_reporting(E_ALL | E_STRICT);

// An API for updating a victim

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/getIP.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

//retrieve user input
$IP = mysqli_real_escape_string($link,getIP());
$victimID = $link->real_escape_string($_GET['victimID']);
$userID = mysqli_real_escape_string($link,$_SESSION['userid']);

//A list of properties that can be updated by an editor.
$PROPERTIES = array('date',
    'name',
    'gender',
    'age',
    'ageGroup',
    'city',
    'state',
    'lat',
    'lng',
    'url',
    'note'
);

function validateDate($date){
    $date_array = explode('-',$date);
    $year = $date_array[0];
    if($year>2013){
        return false;
    }
    if($year<2012){
        return false;
    }
    return true;
}

// Create an array, $pairings, that includes all of the (property name => update value) pairs
$pairings = array();
for($i=0;$i<count($PROPERTIES);$i++){ // for all the properties that can be updated by an editor
    $property = $PROPERTIES[$i];
    if(isset($_GET[$property])){ // if it's set
        $value = mysqli_real_escape_string($link,$_GET[$property]);
        // translate the value to the format with which it is stored in the database
        if($property=='ageGroup'){
            if($value=='child'){$value=1;}
            elseif($value=='teen'){$value=2;}
            elseif($value=='adult'){$value=3;}
        }
        if($property=='date'){
            $value = explode('/',$value); // MM/DD/YYYY only; this is the format the editorial form sends
            $year = $value[2];
            $month = $value[0];
            $day = $value[1];
            $value = $year.'-'.$month.'-'.$day;
            if(!validateDate($value)){
                die();
            }
        }
        if($property=='age'||$property=='lat'||$property=='lng'){
            if($value==''){
                $value='NULL';
            }
        }
        $pairings[$property]="$value";
    }
}
$setString = '';
$i=0;


// build the string that will appear in the set statement
foreach(array_keys($pairings) as $property){
    if($pairings[$property]=='NULL'){
        $setString.="$property=".$pairings[$property].", "; //no single quotes in NULL statements
    }
    else{
        $setString.="$property='".$pairings[$property]."', ";         
    }
}

// find the original values (for the changeLog)
$s = join(',',array_keys($pairings));
$query = "SELECT $s FROM victims WHERE victimID=$victimID";
$fromValues = mysqli_fetch_assoc(mysqli_query($link,$query));

// update the victims table
$query = "UPDATE victims SET $setString lastModified=current_timestamp WHERE victimID=$victimID";

if($link->query($query)){
    // if successful, update the changeLog
    foreach(array_keys($fromValues) as $property){
        $fromValue = $fromValues[$property];
        $toValue = $pairings[$property];
        if($fromValue!=$toValue){
            if($fromValue!='NULL'){
                $fromValue = "'".$fromValue."'";
            }
            if($toValue!='NULL'){
                $toValue = "'".$toValue."'";
            }
            $query = "INSERT INTO changeLog(user,time,property,toValue,fromValue,victimID) VALUES('$userID',current_timestamp,'$property',$toValue,$fromValue,$victimID)";
            $link->query($query);   
        }
    }
};

// if the URL is changing, set the URL to fixed if it has been flagged as "broken"
if(isset($_GET['url'])){
    $q = "UPDATE victims SET broken=0 WHERE victimID=$victimID";
    $link->query($q);
}

$link->close();
echo $_GET['callback'] . '();';

?>