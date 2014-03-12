<?php
header('');
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

echo 'updating';

//connect to database
$link = mysql_connect('quizdb.cvhpc2dxvogu.us-east-1.rds.amazonaws.com', 'quizuser', 'slat3Qu12');
if (!$link) {die('Could not connect: ' . mysql_error());}
@mysql_select_db('gunDeaths') or die( "Unable to select database");

//var_dump($link);
//mysql_query("INSERT into victims(date,name,gender,age,ageGroup,city,state,lat,lng,url,added,lastModified,ip,userID,note) values('2012-12-18','Harvey Ringold','M','26',3,'Chester','PA',39.849557,-75.3557458,'http://www.mainlinemedianews.com/articles/2012/12/19/region/doc50d1c64aea638689827080.txt',current_timestamp,current_timestamp,'::1','cperryk','')");

//retrieve user input
$ip = mysql_real_escape_string(getIP());
$username = 'cperryk';
    function csvToDictionary($fileName){
        $array = array();
        $handle = @fopen($fileName, "r");
        if ($handle) {
            $rowCount = 0;
            $headers = array();
            while (($row = fgetcsv($handle)) !== false) {
                foreach($row as $cell){
                    if($rowCount==0){
                        array_push($headers,$cell);
                    }
                    else{
                        $obj = array();
                        $cellCount = 0;
                        foreach($row as $cell){
                            $array[$rowCount][$headers[$cellCount]] = $cell;
                            $cellCount ++;
                        }
                    }
                }
               $rowCount++;
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }
        return $array;
    }
    $data = csvToDictionary('data.csv');
    
    $i=0;
    foreach($data as $entry){
        if($i<1046){
            $name = str_replace("'","\'",$entry["victim_name"]); 
            $note = str_replace("'","\'",$entry["notes"]);
            $date = $entry["date"];
            echo '<p style="margin-top:40px;">#'."$i, $name, $date".'</p>';
            $date = explode('/',$date);
            $year = $date[2];
            $month = $date[0];
            $day = $date[1];
            $date = $year.'-'.$month.'-'.$day;              
            $gender = $entry["victim_gender"];
            $age = $entry["victim_age"];
            if($age==''){
                $age='NULL';
            }
            $ageGroup;
                if($age=='teen'){
                    $age='NULL';
                    $ageGroup='2';
                }
                elseif($age=='adult'){
                    $age='NULL';
                    $ageGroup='3';
                }
                elseif($age=='child'){
                    $age='NULL';
                    $ageGroup='1';
                }
                elseif($age=='NULL'){
                    $ageGroup='NULL';
                }
                elseif(intval($age)<13){
                    $ageGroup='1';
                }
                elseif(intval($age)<18){
                    $ageGroup='2';
                }
                elseif(intval($age)>=18){
                    $ageGroup='3';
                }
                else{
                    $ageGroup='NULL';
                }
            $city = $entry["city"];
                if($city[strlen($city)-1]==' '){ //delete trailing spaces, which were necessary to circumvent google_geocode rate limits
                    $city = substr($city,0,strlen($city)-1);
                
                    $state = $entry["state"];
                    sleep(2);
                    $geoURL = "http://nominatim.openstreetmap.org/search?format=json&email=chris.kirk@slate.com";
                    if(strpos(strtolower($city),'county')!=false){
                        $c = str_replace(' ','+',$city);
                        $c = str_replace('county','',$city);
                        $c = str_replace('County','',$city);
                        $geoURL.='&county='.str_replace(' ','+',$c);
                    }
                    else{
                        $geoURL.='&city='.str_replace(' ','+',$city);   
                    }            
                    $geoURL.='&state='.str_replace(' ','+',$state).'&country=USA&countrycodes=us';
                    echo '<p style="margin-left:10px;margin-top:0px;margin-bottom:0px;">'."$geoURL".'</p>';
                    $geoContents = file_get_contents($geoURL);
                    //$geoContents = array();
                    echo '<p style="margin-left:10px;margin-top:0px;margin-bottom:0px;background-color:#e3e3e3;">'.$geoContents.'</div>';
                    $geodata = json_decode($geoContents,true);
                    echo count($geodata);
                    if(count($geodata)>0){
                        $lat = $geodata[0]['lat'];
                        $lng = $geodata[0]['lon'];   
                    }
                    else{
                        $lat = $entry["lat"];
                        $lng = $entry["lng"];
                        echo '<p style="margin-left:10px;margin-top:0px;margin-bottom:0px;color:red">FALLING BACK TO GOOGLE GEOCODING</p>';
                        $note.=' GGEO';
                    }
                    
                    echo '<br><br>';
                    
                    $city = str_replace("'","\'",$city);
                    $url = urldecode($entry["source"]);
                    $url = str_replace("'","\'",$url);
                    if(strlen($state)>2){
                        $state = convert_state($state,'abbrev');                
                    }
                    $victimID = $i+1;
                    //$query = "INSERT into victims(date,name,gender,age,ageGroup,city,state,lat,lng,url,added,lastModified,ip,userID,note) values('$date','$name','$gender',$age,$ageGroup,'$city','$state',$lat,$lng,'$url',current_timestamp,current_timestamp,'$ip','$username','$note')";
                    $query  = "UPDATE victims set city='$city', lat=$lat, lng=$lng where victimID=$victimID";
                    echo '<p style="margin-left:10px;margin-top:0px;margin-bottom:0px">'.$query.'</p>';
                    mysql_query($query);
                }
        }
        $i++;
    }

mysql_close();

function getIP() {
    $ip; 
    if(getenv("HTTP_X_FORWARDED_FOR")){ $ip = getenv("HTTP_X_FORWARDED_FOR");}
    else if(getenv("REMOTE_ADDR")){ $ip = getenv("REMOTE_ADDR");}
    else{$ip = "UNKNOWN";}
    return $ip;
}

function convert_state($name, $to='name') {
	$states = array(
	array('name'=>'Alabama', 'abbrev'=>'AL'),
	array('name'=>'Alaska', 'abbrev'=>'AK'),
	array('name'=>'Arizona', 'abbrev'=>'AZ'),
	array('name'=>'Arkansas', 'abbrev'=>'AR'),
	array('name'=>'California', 'abbrev'=>'CA'),
	array('name'=>'Colorado', 'abbrev'=>'CO'),
	array('name'=>'Connecticut', 'abbrev'=>'CT'),
	array('name'=>'Delaware', 'abbrev'=>'DE'),
	array('name'=>'Florida', 'abbrev'=>'FL'),
	array('name'=>'Georgia', 'abbrev'=>'GA'),
	array('name'=>'Hawaii', 'abbrev'=>'HI'),
	array('name'=>'Idaho', 'abbrev'=>'ID'),
	array('name'=>'Illinois', 'abbrev'=>'IL'),
	array('name'=>'Indiana', 'abbrev'=>'IN'),
	array('name'=>'Iowa', 'abbrev'=>'IA'),
	array('name'=>'Kansas', 'abbrev'=>'KS'),
	array('name'=>'Kentucky', 'abbrev'=>'KY'),
	array('name'=>'Louisiana', 'abbrev'=>'LA'),
	array('name'=>'Maine', 'abbrev'=>'ME'),
	array('name'=>'Maryland', 'abbrev'=>'MD'),
	array('name'=>'Massachusetts', 'abbrev'=>'MA'),
	array('name'=>'Michigan', 'abbrev'=>'MI'),
	array('name'=>'Minnesota', 'abbrev'=>'MN'),
	array('name'=>'Mississippi', 'abbrev'=>'MS'),
	array('name'=>'Missouri', 'abbrev'=>'MO'),
	array('name'=>'Montana', 'abbrev'=>'MT'),
	array('name'=>'Nebraska', 'abbrev'=>'NE'),
	array('name'=>'Nevada', 'abbrev'=>'NV'),
	array('name'=>'New Hampshire', 'abbrev'=>'NH'),
	array('name'=>'New Jersey', 'abbrev'=>'NJ'),
	array('name'=>'New Mexico', 'abbrev'=>'NM'),
	array('name'=>'New York', 'abbrev'=>'NY'),
	array('name'=>'North Carolina', 'abbrev'=>'NC'),
	array('name'=>'North Dakota', 'abbrev'=>'ND'),
	array('name'=>'Ohio', 'abbrev'=>'OH'),
	array('name'=>'Oklahoma', 'abbrev'=>'OK'),
	array('name'=>'Oregon', 'abbrev'=>'OR'),
	array('name'=>'Pennsylvania', 'abbrev'=>'PA'),
	array('name'=>'Rhode Island', 'abbrev'=>'RI'),
	array('name'=>'South Carolina', 'abbrev'=>'SC'),
	array('name'=>'South Dakota', 'abbrev'=>'SD'),
	array('name'=>'Tennessee', 'abbrev'=>'TN'),
	array('name'=>'Texas', 'abbrev'=>'TX'),
	array('name'=>'Utah', 'abbrev'=>'UT'),
	array('name'=>'Vermont', 'abbrev'=>'VT'),
	array('name'=>'Virginia', 'abbrev'=>'VA'),
	array('name'=>'Washington', 'abbrev'=>'WA'),
	array('name'=>'West Virginia', 'abbrev'=>'WV'),
	array('name'=>'Wisconsin', 'abbrev'=>'WI'),
	array('name'=>'Wyoming', 'abbrev'=>'WY'),
	array('name'=>'D.C.', 'abbrev'=>'DC'),
        array('name'=>'DC','abbrev'=>'DC')

	);
	$return = false;
	foreach ($states as $state) {
		if ($to == 'name') {
			if (strtolower($state['abbrev']) == strtolower($name)){
				$return = $state['name'];
				break;
			}
		} else if ($to == 'abbrev') {
			if (strtolower($state['name']) == strtolower($name)){
				$return = strtoupper($state['abbrev']);
				break;
			}
		}
	}
	return $return;
}
?>