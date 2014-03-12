<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
function isLoggedIn(){
    session_start();
    if(isset($_SESSION['valid']) && $_SESSION['valid'])
        return true;
    return false;
}
$loggedIn=isLoggedIn();
$pass = true;

if($loggedIn==false){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        //connect to database
        require_once($_SERVER['DOCUMENT_ROOT'].'/connectToDatabase.php');
        $link->select_db('gunDeaths');
        $query = "SELECT password, salt, verified FROM users WHERE username = '$username';";
        
        $result = mysqli_query($link,$query);
        if(mysqli_num_rows($result)<1){
            echo '<p>no such user exists';
            $pass=false;
        }
        $userData = mysqli_fetch_array($result,MYSQL_ASSOC);
        mysqli_close($link);
        $hash = hash('sha256', $userData['salt'] . hash('sha256', $password) );
        if($hash!=$userData['password']) //incorrect password
        {
            echo '<p>incorrect password';
            $pass=false;
        }
        if($userData['verified']!=true){
            echo '<p>The interactives editor has not approved your registration.';
            $pass=false;
        }
        
        if($pass==true){
            session_regenerate_id (); //this is a security measure
            $_SESSION['valid'] = 1;
            $_SESSION['userid'] = $username;
            header ("location: gunDeathsForm.php");
        }
        else{
            ?>
            <html>
                <head></head>
                <body>
                    <form name="login" action="index.php" method="post">
                        Username: <input type="text" name="username" />
                        Password: <input type="password" name="password" />
                        <input type="submit" value="Login" />
                        <p><a href="register.php">Register</a>
                    </form>
                </body>
            </html>
            <?
        }
    }//end post check
    else{
        ?>
        <html>
            <head></head>
            <body>
                <form name="login" action="index.php" method="post">
                    Username: <input type="text" name="username" />
                    Password: <input type="password" name="password" />
                    <input type="submit" value="Login" />
                    <p><a href="register.php">Register</a>
                </form>
            </body>
        </html>
        <?
    }
}
else{
    header ("location: gunDeathsForm.php");
}

?>