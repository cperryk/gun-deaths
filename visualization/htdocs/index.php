<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php

/* 
    This file is used to generate HTML that is then copy and pasted into an HTML component of Slate's CMS. It is NOT meant to run live.
*/

//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
$MIN = false;
$PATH = 'http://www.slate.com/features/2013/gunDeaths/';
$LIB_PATH = 'http://slate.com/features/2013/lib/';
?>

<html>
    <head>
        <script type="text/javascript" src="lib/jquery-1.8.2.min.js"></script>
        <title>Gun Deaths Visualization</title>
    </head>
    <body style="padding:0px;margin:0px">
        
        <div id="wrapper" style="width:920px;margin-left:auto;margin-right:auto">
        
        <!-- paste into RenderHTML-->
        <!--leaflet stuff-->
        <script type="text/javascript" src="<?=$LIB_PATH?>leaflet-0.6.3/leaflet.js"></script>
        <link rel="stylesheet" href="<?=$LIB_PATH?>leaflet-0.6.3/leaflet.css"/>
        <!--[if lte IE 8]>
            <link rel="stylesheet" href="<?=$LIB_PATH?>leaflet-0.6.3/leaflet.ie.css"/>
        <![endif]-->
        
        <?='<script type="text/javascript">'?>
        //<![CDATA[
            <?=file_get_contents('gun-deaths-3.0'.($MIN?'.min':'').'.js')?>
        //]]>
        <?='</script>'?>
        
        
        
        <style type="text/css">
            <?   
            echo str_replace("url(",("url(".$PATH),file_get_contents('gun-deaths-3.0'.($MIN?'.min':'').'.css'));
            ?>
        </style>
        
        
        
        
        <div id="interactive">
            <div id="tooltip">
                <div id="branch">
                    <div id="arrow"></div>
                    <div id="floater">
                        <div id="textArea">
                            <div id="loadingTooltip">Loading...</div>
                            <div id="tooltipData">
                                <p class="victim_information_p" id="tooltip_name"><span id="name_here"></span></p>
                                <p class="victim_information_p">Killed in <span id="location_here" class="tooltip_filter"></span></p>
                                <p class="victim_information_p">Shot on <span id="date_here" class="tooltip_filter"></span></p>
                                <p class="victim_information_p" id="tooltip_age">Age: <span id="age_here"></span></p>
                                <p class="victim_information_p">Source: <a id="source_here" target="_blank"></a></p>
                                <div id="victim_cats"></div>
                                <p id="linkp"><a target="_blank" id="btn_categorize_incident">Categorize</a> | <a target="_blank" id="btn_reportError">Report Error / Additional Info</a></p>
                            </div>
                        </div>
                        <div id="miniMap">

                        </div>
                    </div>
                </div><!--end branch -->
            </div><!--end tooltip -->
            
            <div id="mapContainer">
                <p id="map_explanation">Click a marker below to filter incidents by that location. Shows only the 1,000 locations with the most deaths.</p>
                <p id="map_attribution">&#169; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors</p>
            </div>
            <? printFilters(); ?>
            <p id="count">Matched Deaths: <span id="count_here"></span> or more since Newtown</p>
            <div id="loading"><img src="<?=$PATH?>/graphics/loader.gif"/>Fetching latest data</div>
            <div id="victims_wrapper">
                <div id="victims">
            </div>
            </div><!--end days-->
            <p id="p_methodology"><span id="btn_methodology">Show Methodology</span></p>
            <div id="methodology">
                <p>Each person under 13 years of age is designated "child"; from 13 to 17: "teen"; 18 and older: "adult."</p>
                <p>The same icons used to represent males is also used to represent individuals of unknown gender. The same icons used to represent adults is also used to represent people of unknown age group.</p>
                <p>The yellow and blue backgrounds represent alternating days.</p>
                <p>The information is collected by volunteers from news reports about the deaths. The Slate interactives team and these volunteers continually manage and revise the data.</p>
                <p>The data are not comprehensive because not all gun-related deaths are reported by the news media. For example, suicides often go unreported.</p>
            </div>
        </div><!--end interactive -->
       
       
    <!--stop paste-->

    <!--end wrapper-->
    
        
    </body>
</html>

<? function printFilters(){
?>
                <div id="filters">

                <div id="filter_ageGroup" unselectable="on" class="filterGroup" data-property="ageGroup">
                    <div id="btn_ageGroup" unselectable="on" data-property="agegroup" class="filterHead"><a>Any Age Group</a><span class="dwnarw"></span></div>
                    <div id="filterBox_agegroup" unselectable="on" class="filterBox" data-property="agegroup">
                        <ul>
                            <li class="standard active" unselectable="on" data-value="null">Any Age Group</li>
                            <li class="standard" unselectable="on" data-value="3">Adult</li>
                            <li class="standard" unselectable="on" data-value="2">Teen</li>
                            <li class="standard" unselectable="on" data-value="1">Child</li>
                        </ul>
                    </div>
                </div>
                    
                    <!-- gender filter-->
                    <div id="filter_gender" unselectable="on" class="filterGroup" data-property="gender">
                        <div id="btn_gender" unselectable="on" data-property="gender" class="filterHead"><a>Any Gender </a><span class="dwnarw"></span></div>
                        <div id="filterBox_gender" unselectable="on" class="filterBox" data-property="agegroup">
                            <ul>
                                <li class="standard active" unselectable="on" data-value="null">Any Gender</li>
                                <li class="standard" unselectable="on" data-value="M">Male</li>
                                <li class="standard" unselectable="on" data-value="F">Female</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!--location filter-->
                    <div id="btn_ex_location" class="btn_ex" data-property="location">&#215;</div>
                    <div id="filter_location" class="filterGroup" data-property="location">
                        <div id="btn_location" data-property="location" class="filterHead"><a>Any Location </a><span class="dwnarw"></span></div>
                        <div id="filterBox_location" class="filterBox" data-property="location">
                            <ul>
                                <li class="ti">City: <input type="text" id="icity"/></li>
                                <li class="ti">State: <input type="text" id="istate"/></li>
                                <li class="locative btnok">OK</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!--location date-->
                    <div id="btn_ex_date" class="btn_ex" data-property="date">&#215;</div>
                    <div id="filter_date" class="filterGroup" data-property="date">
                        <div id="btn_date" data-property="date" class="filterHead"><a>Any Date </a><span class="dwnarw"></span></div>
                        <div id="filterBox_date" class="filterBox" data-property="location">
                            <ul>
                                <li class="ti">Min Date: <input type="text" id="imindate"/></li>
                                <li class="ti">Max Date: <input type="text" id="imaxdate"/></li>
                                <li class="date btnok">OK</li>
                            </ul>
                        </div>
                    </div>

                    <div id="filter_keyword" class="filterGroup" data-property="keyword">
                        <div id="btn_keyword" data-property="keyword" class="filterHead"><a>No Keywords </a><span class="dwnarw"></span></div>
                        <div id="filterBox_keywords" class="filterBox" data-property="keyword">
                            <ul>
                                <li class="ti">Keywords: <input type="text" id="ikeyword"/></li>
                                <li class="keyword btnok">OK</li>
                            </ul>
                        </div>
                    </div> 
                </div><!-- end filters-->
<?
}?>