<?php
header('content-type:application/x-javascript');
//ini_set('display_errors', 'On'); error_reporting(E_ALL | E_STRICT);

// Check if the user is logged in.
require('checkIfLoggedIn.php');

// The URL to the front-end interactive, which is included in the automatic tweet
$SLATE_ARTICLE_URL = 'http://www.slate.com/articles/news_and_politics/crime/2012/12/gun_death_tally_every_american_gun_death_since_newtown_sandy_hook_shooting.html';

// Connect to the victims database
require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php'); // A script that connects to Slate's interactives database
$link->select_db('gunDeaths');

//retrieve user input
$IP = mysqli_real_escape_string($link,getIP()); // The IP of the computer adding the victim
$date = mysqli_real_escape_string($link,$_GET['date']);
$name = mysqli_real_escape_string($link,$_GET['name']);
$gender = mysqli_real_escape_string($link,$_GET['gender']);
$age = mysqli_real_escape_string($link,$_GET['age']);
$ageGroup = mysqli_real_escape_string($link,$_GET['ageGroup']);
$city = mysqli_real_escape_string($link,$_GET['city']);
$state = mysqli_real_escape_string($link,$_GET['state']);
$lat = mysqli_real_escape_string($link,$_GET['lat']);
$lng = mysqli_real_escape_string($link,$_GET['lng']);
$url = mysqli_real_escape_string($link,$_GET['url']);
$note = mysqli_real_escape_string($link,$_GET['note']); 
$userID = mysqli_real_escape_string($link,$_SESSION['userid']); // The ID of the user adding the victim
$tweet = $_GET['tweet']; // Whether the victim should be tweeted or not

// add victim to the database
addVictimToDatabase($IP,$date,$name,$gender,$age,$ageGroup,$city,$state,$lat,$lng,$url,$note,$userID);

// tweet the victim if "tweet" is true
if($tweet=='true'){
    tweet_victim($date,$name,$age,$ageGroup,$city,$state,$url,$gender);
}

// check if the total victim count is now a multiple of 100, and tweet it out if it is
checkCountAndPost();

mysqli_close($link);
echo $_GET['callback'] . "(".");";

//functions
function checkCountAndPost(){ // If the victim count has reached a multiple of 100, tweet that factoid out.
    global $SLATE_ARTICLE_URL;
    function get_total_count(){
        global $link;
        $query = 'Select count(*) from victims where deleted=false and isNewtown=false';
        $count = mysqli_fetch_assoc(mysqli_query($link,$query));
        $count = $count['count(*)'];
        return $count;
    }
    $count = get_total_count();
    $divisibleBy100 = $count%100==0?true:false;
    if($divisibleBy100){
        $tweet = 'There have been at least '.number_format($count).' gun-related deaths since Newtown '.$SLATE_ARTICLE_URL;
        post_tweet($tweet);
    }
}
function addVictimToDatabase($IP,$date,$name,$gender,$age,$ageGroup,$city,$state,$lat,$lng,$url,$note,$userID){
    global $link;
    $lat = $lat==''?'NULL':$lat;
    $lng = $lng==''?'NULL':$lng;
    $age = $age==''?'NULL':"'".$age."'";
    $ageGroupNumber = $ageGroup=="child"?1:($ageGroup=="teen"?2:($ageGroup=="adult"?3:'NULL'));
    $new_date = MDYtoYMD($date);
    if(!validateDate($new_date)){
        die();
    }
    $query = "INSERT into victims(date,name,gender,age,ageGroup,city,state,lat,lng,url,note,added,lastModified,userID) values('$new_date','$name','$gender',$age,$ageGroupNumber,'$city','$state',$lat,$lng,'$url','$note',current_timestamp,current_timestamp,'$userID')";
    mysqli_query($link,$query);
}
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

function MDYtoYMD($date){
    $date_array = explode('/',$date);
    $year = $date_array[2];
    $month = $date_array[0];
    $day = $date_array[1];
    return $year.'-'.$month.'-'.$day;
}
function getIP() {
    $ip; 
    if(getenv("HTTP_X_FORWARDED_FOR")){ $ip = getenv("HTTP_X_FORWARDED_FOR");}
    else if(getenv("REMOTE_ADDR")){ $ip = getenv("REMOTE_ADDR");}
    else{$ip = "UNKNOWN";}
    return $ip;
}
function tweet_victim($date,$name,$age,$ageGroup,$city,$state,$url,$gender){
    //if age is set, add '(age)-year-old' to appear before noun
    $s = '';
    if($age===0){
        if($gender=='M'){
            $s.='Infant boy';
        }
        elseif($gender=='F'){
            $s.='Infant girl';
        }
        else{
            $s.='Infant';
        }
    }
    elseif($name==''){ //if no name
        if($age==''){ //if no age
            if($ageGroup==''){ //if no age group
                if($gender=='M'){
                    $s.='Man'; //default to adult gender types.
                }
                elseif($gender=='F'){
                    $s.='Woman';
                }
                else{
                    $s.='Person';
                }
            }
            else{ //if ageGroup
                if($gender==''){
                    if($ageGroup=='adult'){
                        $s.='Person';
                    }
                    else{
                        $s.=ucwords($ageGroup);
                    }
                }
                else{ //if gender
                    if($ageGroup=='adult'){
                        if($gender=='M'){
                            $s.='Man';
                        }
                        elseif($gender=='F'){
                            $s.='Woman';
                        }
                    }
                    elseif($ageGroup=='child'||$ageGroup=='teen'){
                        if($gender=='M'){
                            $s.='Boy';
                        }
                        elseif($gender=='F'){
                            $s.='Girl';
                        }
                    }
                }
            }
        }
        else{ //if age
            if($gender==''){ //if no gender
                $s.=$age.'-year-old';
            }
            else{ //if gender
                if($ageGroup=='teen'||$ageGroup=='child'){
                    if($gender=='M'){
                        $s.=$age.'-year-old boy';
                    }
                    else{
                        $s.=$age.'-year-old girl';
                    }
                }
                elseif($ageGroup=='adult'){
                    if($gender=='M'){
                        $s.='Man, '.$age.',';
                    }
                    elseif($gender=='F'){
                        $s.='Woman, '.$age.',';
                    }
                }
            }
        }
    }
    else{ //if name
        if($age==''){
            $s.=$name;
        }
        else{
            $s.=$name.', '.$age.',';
        }
    }

    $date = explode('/',$date);

    //build string
    $s = $date[0].'/'.$date[1].': '.$s.' shot dead in '.$city.', '.$state.' '.$url;
    $posted = postTweet($s);
    if(isset($_GET['debug'])){
        echo $s;
        var_dump($posted);
    }
}
function postTweet($tweet_text) { // post a tweet to an account
    require_once("tmhOAuth.php");
    require_once("twitter_credentials.php");
    /* Pulls in consumer key, consumer secret, user token, and user secret for
    various Slate accounts. Like so:

    $credentials = array(
        '@gunDeaths'=>array(
            'consumer_key' => "????????????????",
            'consumer_secret' => "????????????????",
            'user_token' => "????????????????",
            'user_secret' => "????????????????"
        )
    );
    */
    $connection = new tmhOAuth($credentials['@gunDeaths']);
    $connection->request('POST',
        $connection->url('1.1/statuses/update'),
        array('status' => $tweet_text));
    return $connection->response;
}
?>