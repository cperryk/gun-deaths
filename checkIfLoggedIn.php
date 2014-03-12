<?php

function isLoggedIn(){
    session_start();
    if(isset($_SESSION['valid']) && $_SESSION['valid'])
        return true;
    return false;
}
if(isLoggedIn()!=true){
    echo 'Error: You are not logged in! To log in, go here: <a href="http://slate-interactives-prod.elasticbeanstalk.com/gun-deaths/form/">http://slate-interactives-prod.elasticbeanstalk.com/gun-deaths/form/</a>';
    exit();
}

?>