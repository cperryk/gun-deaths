<?php
function logout()
{
    session_start();
    $_SESSION = array(); //destroy all of the session variables
    session_destroy();
}
logout();
?>

<html>
    <head>
        <title>Logged out</title>
    </head>
    <body>
    <p>You're logged out.
    <p><a href="index.php">Log in again</a>
    </body>
</html>