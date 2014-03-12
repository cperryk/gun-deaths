<html>
    <head></head>
    <body>

<form name="register" action="register.php" method="post" style="width:300px;margin-left:auto;margin-right:auto;padding:10px;border:1px solid #e3e3e3;font-family:verdana,sans-serif">
    <h1 style="margin-top:0px;font-size:20px;">Register for Gun Deaths</h1>
    <p>Username: <input type="text" name="username" maxlength="30" /></p>
    <p>Password: <input type="password" name="pass1" /></p>
    <p>Password Again: <input type="password" name="pass2" /></p>
    <input type="submit" value="Register" />
</form>

    </body>
</html>


<?php

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    //retrieve our data from POST
    $username = $_POST['username']; 
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    $pass = true;
    if($pass1 != $pass2){
        echo '<p>The two passwords you entered did not match';
        $pass=false;
    }
    if(strlen($username) > 15){
        echo '<p>The username you entered was too long. If must be no longer than 15 characters.';
        $pass=false;
    }
    if($pass==true){
        $hash = hash('sha256', $pass1);
        
        //creates a 3 character sequence
        /*
        function createKeycode(){
            $string = md5(uniqid(rand(),true));
            return substr($string,0,11);
        }*/
        
        function createSalt()
        {
            $string = md5(uniqid(rand(), true));
            return substr($string, 0, 3);
        }
        $salt = createSalt();
        $hash = hash('sha256', $salt . $hash);
        //$keycode = createKeycode();
        
        $link = mysql_connect('quizdb.cvhpc2dxvogu.us-east-1.rds.amazonaws.com', 'quizuser', 'slat3Qu12');
        if (!$link) {die('Could not connect: ' . mysql_error());}
        @mysql_select_db('gunDeaths') or die( "Unable to select database");
        
        //sanitize username
        $username = mysql_real_escape_string($username);
        $query = "INSERT INTO users ( username, password, salt)
                VALUES ( '$username' , '$hash' , '$salt');";
        mysql_query($query);
        mysql_close();
        
        echo "Your username was successfully created. Please contact the interactives editor for approval, then <a href=\"index.php\">log in</a>.";
        
        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        
        // Additional headers
        $headers .= 'To: Chris Kirk <chris.kirk@slate.com>' . "\r\n";
        $headers .= 'From: Gun Deaths Database' . "\r\n";
        
        mail('chris.kirk@slate.com','A new user has been added to the Gun Deaths database.','The user is '.$username,$headers);
        
        
    }//end pass check
}//end post check

?>