<?php

function isLoggedIn(){
    session_start();
    if(isset($_SESSION['valid']) && $_SESSION['valid'])
        return true;
    return false;
}
if(isLoggedIn()!=true){
    echo 'Error: You are not logged in! To log in, go <a href="form/">here</a>.';
    exit();
}

?>