<?php
header('content-type:text/csv');
header("Content-Disposition: attachment; filename=SlateGunDeaths.csv");
header("Pragma: no-cache");
header("Expires: 0");

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');
$result = $link->query('SELECT victimID,url,name,gender,age,city,state,`date` FROM victims WHERE deleted=0 AND name!="" AND broken = 1');

echo 'victimID,url,name,gender,age,city,state,date'."\n";
function convert_smart_quotes($string) 
{ 
    $search = array(chr(145), 
                    chr(146), 
                    chr(147), 
                    chr(148), 
                    chr(151)); 

    $replace = array("'", 
                     "'", 
                     '"', 
                     '"', 
                     '-'); 

    return str_replace($search, $replace, $string); 
}

while($r = mysqli_fetch_assoc($result)) { //for each victim
    $victim = array();
    $count=0;
    foreach(array_keys($r) as $prop){
        $r[$prop] = convert_smart_quotes($r[$prop]);
        if($r[$prop]==''){
            $val = '(UNKNOWN)';
        }
        else{
            $val = $r[$prop];
        }
        $val = str_replace('"', '||', $val);
        echo '"'.$val.'"';
        if($count<8){
            echo ",";
            $count++;
        }
    }
    echo "\n";
}
mysqli_close($link);


?>