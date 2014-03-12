<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
//$path = './';
$interactiveName = 'gunCount';
?>

<html>
    <head>
        <script type="text/javascript" src="jquery-1.8.2.min.js"></script>
        <title><? echo $interactiveName?></title>
    </head>
    <body style="padding:0px;margin:0px">

        <!-- paste into RenderHTML -->
        <script type="text/javascript">

        //<![CDATA[
            <? echo file_get_contents('gunCount.js'); ?>
        //]]>

        </script>
   
        <span id="count_here"></span> people have died from 12/14/2012 to <span id="date_here"></span>
        
    </body>
</html>