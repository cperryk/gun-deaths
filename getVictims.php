<?php
header('content-type:application/x-javascript');
//ini_set('display_errors', 'On'); error_reporting(E_ALL | E_STRICT);

/* 

This is an API to serve victim information to the editorial interface or front-end interactive.

    PARAMETERS
    -fields: the fields to return for the user, comma-delimited
    -compact: set to true to return the victim properties without property keys for a smaller response
    -keyword: keyword to match victims
    -limitx: start returning at this number of results
    -limit: number of victims to return
    -categorizations: return the votes of the categories for this victim

    PLUS, all the variables in SEARCH_PROPERTIES below.

    Example query string:

        http://slate-interactives-prod.elasticbeanstalk.com/gun-deaths/getVictims.php?fields=date,name,gender,age,ageGroup,city,state,lat,lng,url,victimID,note,broken&orderBy=lastModified&limitx=0&callback=?

*/

//connect to database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
$link->select_db('gunDeaths');

// A list of properties the user is ALLOWED to search victims for
$SEARCH_PROPERTIES = array(
    'date', // MM/DD/YYYY or YYYY-MM-DD
    'name',
    'gender',
    'age',
    'ageGroup',
    'city',
    'state',
    'lat',
    'lng',
    'url',
    'minDate', // YYYY-MM-DD
    'maxDate', // YYYY-MM-DD
    'note',
    'tweeted',
    'victimID',
    'lastModified',
    'broken' // Interal use. "Broken" victims were flagged by Mechanical Turk users as having broken / expired URLs
);

$fields = $link->real_escape_string($_GET['fields']); // The properties the user wants to get about the victims searched for
$query = 'SELECT '.$fields.' FROM victims';

function getKeyword(){ // Add to the query string where clause the conditions to match a specified keyword if one is provided (e.g. the user searches for "James")
    global $where, $link;
    if(isset($_GET['keyword'])==true){
        $s = '';
        if($where==''){
            $s.=' and ';
        }
        else{
            $s.=' and ';
        }
        $keys = explode(' ',$_GET['keyword']);
        $count = 0;
        foreach($keys as $key){
            if($count>0){
                $s.=' and ';
            }
            $k = "'%".$link->real_escape_string($key)."%'";
            $s.= '(city LIKE '.$k;
            $s.= ' or name LIKE '.$k;
            $s.= ' or url LIKE '.$k;
            $s.= ' or note LIKE '.$k;
            $s.= ')';
            $count++;
        }
        return $s;
    }
    else{
        return '';
    }
}
function MMDDYYYYtoYYYYMMDD($dateString){
    $value = explode('/',$dateString);
    $year = $value[2];
    $month = $value[0];
    $day = $value[1];
    $value = $year.'-'.$month.'-'.$day;
    return $value;
}
function getWhere(){ // Start the WHERE clause of the query string
    global $SEARCH_PROPERTIES,$link;
    $pairings = array();
    $whereString = ' WHERE deleted=0';
    for($i=0;$i<count($SEARCH_PROPERTIES);$i++){ // For each allowed search property
        $property = $SEARCH_PROPERTIES[$i];
        if(isset($_GET[$property])){ // if the user is searching with it
            $value = $_GET[$property]; // the value the user is searching for
            // based on the property, convert that variable into the appropriate WHERE conditional
            if($property=='ageGroup'){
                if($value=='child'){$value=1;}
                elseif($value=='teen'){$value=2;}
                elseif($value=='adult'){$value=3;}
            }
            if($property=='date'||$property=='minDate'||$property=='maxDate'){
                if(strpos($value,'/')>-1){
                    $value = MMDDYYYYtoYYYYMMDD($value);
                }
            }
            if($property=='minDate'){
                $whereString.=" AND date>=date('".$value."')";
            }
            elseif($property=='maxDate'){
                $whereString.=" AND date<=date('".$value."')";
            }
            elseif($property=='victimID'){
                $whereString.=" AND victimID='".$value."'";
            }
            elseif($property=='lat'||$property=='lng'){
                $whereString.=' AND '.$property.'='.$value;
            }
            else{
                $whereString.=' AND '.$link->real_escape_string($property).'='."'".$link->real_escape_string($value)."'";
            }
        }
    }
    return $whereString;
}
function getLimit(){
    global $link, $SEARCH_PROPERTIES;
    if(isset($_GET['limitx'])==true){
        return " LIMIT ".$link->real_escape_string($_GET['limitx']).', 100';
    }
    return "";
}
function getSort(){
    if(isset($_GET['sort'])==true){
        if($_GET['sort']=='asc'){
            return '';
        }
    }
    return ' DESC';
}
function getOrderBy(){
    global $link, $SEARCH_PROPERTIES;
    $orderBy = ' ORDER BY';
    if(isset($_GET['orderBy'])){
        if(in_array($_GET['orderBy'],$SEARCH_PROPERTIES)==true){
            $orderBy.=' '.$link->real_escape_string($_GET['orderBy']).getSort().',';
        }
    }
    $orderBy.=' isNewtown, date DESC, (ageGroup IS NOT NULL), FIELD(ageGroup,3,2,1), gender DESC';
    return $orderBy;
}

$query.=getWhere().getKeyword().getOrderBy().getLimit();
$result = $link->query($query);

//process data
$victims_result = array();
while($r = $result->fetch_assoc()) {
    $victim = array();
    foreach(array_keys($r) as $prop){
        if(isset($_GET['compact'])){
            array_push($victim,$r[$prop]);
        }
        else{
            $victim[$prop] = $r[$prop];
        }
    }
    array_push($victims_result,$victim);
}

if(isset($_GET['categorizations'])&&isset($_GET['victimID'])){
    $cats = getCategories($_GET['victimID']);
    $victims_result[0]['categorizations'] = $cats;
}


function getCategories($victimID){ // Get the crowdsourced categorizations for a victim
    global $link;
    $out = array();
    $result = $link->query("SELECT category,count(*) FROM cat_responses WHERE victimID = $victimID GROUP BY category");
    while($assoc = $result->fetch_assoc()){
        $out[$assoc['category']]=$assoc['count(*)'];
    }
    return $out;
}

$link->close();
$json = json_encode($victims_result);
echo $_GET['callback'] . '(' . $json . ');';
?>