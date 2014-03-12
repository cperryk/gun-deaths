<?php
require('../checkIfLoggedIn.php');
$INTERACTIVE_NAME = 'gunDeathsForm';
$PROPERTY_DATA = json_decode(file_get_contents('propertyData.json'),true);
ini_set('display_errors', 'On');
error_reporting(E_ALL);

?>

<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
    <head>
        <script type="text/javascript" src="jquery-1.8.2.min.js"></script>
        <script type="text/javascript" src="OpenLayers.js"></script>
        <title>Gun Deaths Form</title>
        <link rel="shortcut icon" href="gun-favicon.png" />
    </head>
    <body>

        <!-- paste into RenderHTML-->
        
        <script type="text/javascript">
            //<![CDATA[
                var userID = '<?=$_SESSION['userid']?>'
                var victimProperties = <?=file_get_contents('propertyData.json')?>;
                var dayMappings = <?=file_get_contents('dayMappings.json')?>;
            //]]>
        </script>
        
        <!--header scripts-->
        <script type="text/javascript">
            //<![CDATA[
            <?=file_get_contents("$INTERACTIVE_NAME.js");?>
            //]]>
        </script>
        <style type="text/css">
            <?=file_get_contents("$INTERACTIVE_NAME.css")?>
        </style>
        
            <div id="userInfo">
                <a href="tutorial.html">Tutorial</a> | 
                Logged in as <?=$_SESSION['userid'] ?>
                <a href="logout.php">Log out</a>
            </div>
        
        <div id="interactive">
        
            
            <!--<div id="btn_openAdd">Add a new victim</div>-->

            <div id="editContainer">
                <div id="btnGroup">
                    <div id="btn_addMode" class="btn active"><img src="graphics/plus-icon.png"/>Add</div>
                    <div id="btn_searchMode" class="btn"><img src="graphics/search-icon.png"/>Search</div>
                </div>
                <div id="editBox">
                    <h3 class="add">Add a new victim <span class="btn_reset">reset</span></h3>
                        <div class="parameters">
                            <?php
                                foreach(array_keys($PROPERTY_DATA) as $property){
                                    if(!isset($PROPERTY_DATA[$property]['onAdd'])||$PROPERTY_DATA[$property]['onAdd']=="true"){
                                        ?>
                                        <div class="<?=$PROPERTY_DATA[$property]['edit']=='true'?'editable':'noEdit'?>">
                                        <span><?=(isset($PROPERTY_DATA[$property]['display'])?$PROPERTY_DATA[$property]['display']:$property)?>: </span>
                                        <input data-property="<?=$property?>" 
                                            class="<?=$property.($PROPERTY_DATA[$property]['required']=="true"?" required":"")?>"
                                            type="text" 
                                            <?=($PROPERTY_DATA[$property]['edit']=="false"?'disabled="disabled"':'')?>
                                            <?=(isset($PROPERTY_DATA[$property]['maxChar'])==true?'maxlength="'.$PROPERTY_DATA[$property]['maxChar'].'"':'')?>/>
                                        </div>
                                        <?
                                    }
                            }
                            ?>
                            <div><input id="tweet_checkbox" type="checkbox" data-property="tweet" checked="checked">Tweet on @GunDeaths</div>

                        </div><!--end parameters-->
                        <div id="btn_add">
                            Add
                        </div>
                </div><!--end editbox-->
                
                <div id="searchBox">
                    <h3 class="add">Search for a victim <span class="btn_reset">reset</span></h3>
                    <div class="parameters">
                        <div class="editable">
                            <span>Quick Search:</span><input type="text" data-property="keyword" style="width:100%"/>
                        </div>
                        <?php
                        foreach(array_keys($PROPERTY_DATA) as $property){ ?>
                            <div class="editable">
                            <span><?=(isset($PROPERTY_DATA[$property]['display'])?$PROPERTY_DATA[$property]['display']:$property)?>:</span>
                            <input data-property="<?=$property?>" class="<?=$property?>" type="text" <?=(isset($PROPERTY_DATA[$property]['maxChar'])==true?'maxlength="'.$PROPERTY_DATA[$property]['maxChar'].'"':'')?>/>
                            </div>
                        <? } ?>
                    </div><!--end parameters-->
                </div>
                
                <div id="tweetBox">
                    <h6>Tweet it on your own</h6>
                    <p id="alreadyTweeted">You have already made a tweet with this URL</p>
                    <p>Custom Head: <input type="text" class="headline"/></p>
                    <p>Via: <input type="text" class="tweeter"/></p>
                    
                    <textarea disabled="disabled"></textarea>
                    <div id="tweetshare" class="sharebox">
                        <img src="graphics/icon_tweet.png"/>
                        <p>Tweet</p>
                    </div>
                </div>
            </div><!--end editContainer-->
            
            <div id="rightSide">
                <h3 class="searchMode">Search Results</h3>
                <h3 class="addMode">Similar Entries <span>(matching date and location)</span></h3>
                <p id="searchDescription"></p>
                <div id="list"></div><!--end list-->
                <div id="btn_previousPage">&#0171; Previous Page</div>
                <div id="btn_nextPage">Next Page &#0187;</div>
            </div>
        </div><!--end addContainer-->
    </body>
</html>

<?


?>