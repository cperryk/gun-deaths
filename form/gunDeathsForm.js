//var serviceRoot = 'http://local.server.com/gun-deaths/';
var serviceRoot = 'http://slate-interactives-prod.elasticbeanstalk.com/gun-deaths/';
var servicePaths = {
    getVictims: serviceRoot + 'getVictims.php?fields=date,name,gender,age,ageGroup,city,state,lat,lng,url,victimID,note,broken&orderBy=lastModified',
    addVictim: serviceRoot + 'addVictim.php?tweet=true',
    updateVictim: serviceRoot + 'updateVictim.php?',
    deleteVictim: serviceRoot + 'deleteVictim.php?',
    getHistory: serviceRoot + 'getHistory.php?'
};
var lastAdded ={};
var resultsPerPage = 100;
var tweet_url;
var tweet_text;
var tweet_via;

$(function(){

    /**INITIALIZATION**/
    $('#interactive input').val(''); //some browsers save all input values, so must manually reset
    var mode = 'add'; //default mode on opening the page
    var modeOpt ={
        'add':{
            'filters':{},
            'orderBy': 'city',
            'sort': 'asc'
        },
        'search':{
            'limitx': 0,
            'filters':{}
        }
    };
    getVictims();
    addEventListeners();

    function addEventListeners(){
        $('#btn_openAdd').click(function(){
            openAdd();
        });
        $('#searchDescription').on('click','span',function(){
            if(mode == 'search'){
                var type = $(this).data('type');
                if(type == 'filters'){
                    var property = $(this).data('property');
                    modeOpt[mode][type] ={};
                    $('#searchBox').find('.' + property).val('');
                }
                else{
                    delete modeOpt[mode][type];
                }
                getVictims();
            }
        });
        $('#list')
            .on('mouseup','.tableHead',function(){ //sorts the table
                var newOrder = $(this).data('property');
                if(modeOpt[mode]['orderBy'] == newOrder){
                    if(modeOpt[mode]['sort'] === undefined){
                        modeOpt[mode]['sort'] = 'asc';
                    }
                    else{
                        modeOpt[mode]['sort'] = undefined;
                    }
                }
                else{
                    modeOpt[mode]['orderBy'] = newOrder;
                    modeOpt[mode]['sort'] = undefined;
                }
                getVictims();
            })
            .on('mouseup','.editRow',function(){
                $(this).closest('.victim').allowEdit();
            })
            .on('mouseup','.saveRow:not(.disabled)',function(){
                $(this).closest('.victim').saveRow();
            })
            .on('mousedown','.deleteRow',function(){
                $(this).closest('.victim').deleteRow();
            })
            .on('mousedown','.copyRow',function(){
                $(this).closest('.victim').copyRow();
            })
            .on('mousedown','.tweetRow',function(){
                $(this).closest('.victim').tweetRow();
            })
            .on('mousedown','.historyRow',function(){
                $(this).closest('.victim').historyRow();
            })
            .on('mousedown','.btn_revert',function(){
                $(this).parent().revert();
            })
            .on('change','input',function(){
                $(this)
                    .addClass('changed');
                var property = $(this).data('property');
                var victim = $(this).closest('.victim');
                if(victimProperties[property]['tiedTo'] !== undefined){
                    for(var i = 0; i < victimProperties[property]['tiedTo'].length; i++){
                        victim.find('input.' + victimProperties[property]['tiedTo'][i]).addClass('changed');
                    }
                }
                $(this)
                    .validateField();
        })
            .on('mouseup','input.url',function(){
                if($(this).attr('disabled') == 'disabled'){
                    window.open($(this).val(), '_blank');
                }
            });
        $('#editBox')
            .on('change','input',function(){
                $(this).validateField();
                var property = $(this).data('property');
                if(victimProperties[property]!==undefined && victimProperties[property]['addMatch'] == "true"){
                    $(this)
                        .addToFilters(modeOpt[mode][property]);
                    getVictims();
                }
            })
            .on('focus','input.age',function(){
                var left = $(this).position().left;
                var top = $(this).position().top;
                /*
                $('<div id="tooltip">')
                    .css('left',left+50)
                    .css('top',top-30)
                    .html('Type <em>adult</em>, <em>teen</em>, or <em>child</em> here to specify age group if exact age is unknown. If the source identified the victim as a "man" or "woman," the age group is <em>adult</em>.')
                    .appendTo('body');
                    */
            })
            .on('blur','input.age',function(){
                $('#tooltip').remove();
            });
        $('#searchBox')
            .on('change','input',function(){
                modeOpt['search']['limitx'] = 0;
                $(this)
                    .validateField()
                    .addToFilters(modeOpt[mode]);
                getVictims();
            });
        $('#btn_add').click(function(){
            if(!$(this).hasClass('disabled')){
                addVictim();
            }
        });
        $('#btn_searchMode').click(function(){
            switchMode('search');
        });
        $('#btn_addMode').click(function(){
            switchMode('add');
        });
        $('#btn_nextPage').click(function(){
            if(modeOpt[mode]['limitx'] === undefined){
                modeOpt[mode]['limitx'] = resultsPerPage;
            }
            else{
                modeOpt[mode]['limitx'] += resultsPerPage;
            }
            getVictims();
        });
        $('#btn_previousPage').click(function(){
            modeOpt[mode]['limitx'] -= resultsPerPage;
            getVictims();
        });
        $('#tweetBox')
            .on('change','input',function(){
            makeTweet();
        });
        $('.btn_reset').click(function(){
            if(mode=='add'){
                $('#editBox')
                    .find('input')
                        .val('')
                        .end()
                    .find('input[type="checkbox"]')
                        .prop('checked',true)
                    .find('.little').remove();
            }
            else if(mode=='search'){
                $('#searchBox')
                    .find('input').val('').end()
                    .find('.little').remove();
            }
            modeOpt[mode]['filters']={};
            getVictims();
        });
    }

    function switchMode(toMode){
        if(toMode != mode){
            mode = toMode;
            if(toMode == 'search'){
                $('#editBox').hide();
                $('#searchBox').show();
                getVictims();
                $('#btn_addMode').removeClass('active');
                $('#btn_searchMode').addClass('active');
                $('.searchMode').show();
                $('.addMode').hide();
            }
            else if(toMode == 'add'){
                $('#editBox').show();
                $('#searchBox').hide();
                getVictims();
                $('#btn_searchMode').removeClass('active');
                $('#btn_addMode').addClass('active');
                $('.addMode').show();
                $('.searchMode').hide();
            }
        }
    }
    $.fn.addToFilters = function (filterGroup){
        var property = $(this).data('property');
        var value = $(this).val();
        if(property=='date'&&mode=='add'){
            var myDate = parseDate(value);
            var minDate = parseDate(value);
            var maxDate = parseDate(value);
            minDate.setDate(minDate.getDate()-1);
            maxDate.setDate(maxDate.getDate()+1);
            modeOpt[mode]['filters']['minDate'] = toMMDDYYYY(minDate);
            modeOpt[mode]['filters']['maxDate'] = toMMDDYYYY(maxDate);
        }
        else{
            modeOpt[mode]['filters'][property] = value;
        }
        if(value === ''){
            delete modeOpt[mode]['filters'][property];
        }
    };
    function toMMDDYYYY(date){
        //takes a date object and outputs a MM/DD/YYYY string
        return [date.getMonth()+1,date.getDate(),date.getFullYear()].join('/');
    }

    function getAgeGroup(age){
        if((jQuery.isNumeric(age) && age < 150) || age == 'adult' || age == 'teen' || age == 'child'){
            if(age == 'adult' || (jQuery.isNumeric(age) && age >= 18)){
                return 'adult';
            }
            else if(age == 'teen' || (jQuery.isNumeric(age) && age <= 17 && age >= 13)){
                return 'teen';
            }
            else if(age == 'child' || (jQuery.isNumeric(age) && age <= 12)){
                return 'child';
            }
        }
        return '';
    }

    function openAdd(){
        $('#editContainer')
            .find('.add')
            .show()
            .end()
            .show();
    }
    $.fn.allowEdit =function(){
        $(this)
            .find('input')
            .each(function(){
            $(this).enableFieldEdit();
        })
            .end()
            .find('.editRow')
            .remove()
            .end()
            .prepend('<div class="saveRow"></div>');
    };
    $.fn.saveRow =function(){
        var row = $(this);

        function buildQueryString(){
            var victimID = row.data('victimid');
            var s = 'victimID=' + victimID;
            row.find('.changed').each(function(){
                s += '&' + $(this).data('property') + '=' + $(this).val();
            });
            return s;
        }
        if(row.find('.changed').length > 0){
            var p = servicePaths.updateVictim + buildQueryString() + '&callback=?';
            $.ajax({
                url: p,
                dataType: "json",
                success: function (data){},
                error:function(xhr,ajaxOptions,thrownError){
                    console.log(xhr);
                    console.log(ajaxOptions);
                    console.log(thrownError);
                    alert('Error. Database did not update.');
                }
            });
        }
        $(this)
            .find('.saveRow')
            .remove()
            .end()
            .find('input')
            .disableFieldEdit()
            .end()
            .prepend('<div class="editRow"></div>');
    };
    $.fn.historyRow=function(){
        var victim = $(this);
        var victimID = $(this).data('victimid');
        if($('.changes').data('victimid')==victimID){
            $('.changes').remove();
        }
        else{
            $('.changes').remove();
            var p = servicePaths.getHistory+'victimID='+victimID+'&callback=?';
            $.ajax({
                url:p,
                dataType:"json",
                success:function(data){
                    var p;
                    var newChanges = $('<div class="changes" data-victimid="'+victimID+'">');
                    if(data.length>0){
                        for(var i=0;i<data.length;i++){
                            var c=data[i];
                            var fromValue = c['fromValue'];
                            if(fromValue===''){
                                fromValue='unknown';
                            }
                            p = $('<p class="change">&#187; <span>'+c['user']+'</span> changed <span class="prop">'+c['property']+'</span> from <span>'+decodeData(c['property'],fromValue)+'</span> to <span class="toValue">'+decodeData(c['property'],c['toValue'])+'</span> on <span>'+c['time']+'</span></p>');
                            newChanges.append(p);
                        }
                    }
                    else{
                        p = $('<p>There is no history on this victim.</p>');
                        newChanges.append(p);
                    }
                    victim.after(newChanges);
                },
                error:function(){
                    alert('Error. Could not get history.');
                }
            });
        }
        return this;
    };
    $.fn.revert = function(){
        var changeRow = $(this);
        var victimID = $(this).closest('.changes').data('victimid');
        var property = $(this).find('.prop').html();
        var toValue = $(this).find('.toValue').html();
        var p = servicePaths.updateVictim + 'victimID='+victimID + '&' + property + '=' +toValue + '&callback=?';
        $.ajax({
            url: p,
            dataType: "json",
            success: function (){
                changeRow.parent().prev().historyRow();
            },
            error:function(){
                alert('Error. Database did not update.');
            }
        });
    };
    $.fn.enableFieldEdit =function(){
        if(this.data('edit')===true){
            this.removeAttr('disabled','disabled');
        }
        $(this).find('changed').removeClass('changed');
        return this;
    };
    $.fn.disableFieldEdit =function(){
        this.attr('disabled','disabled');
        return this;
    };
    $.fn.deleteRow =function(){
        var r = confirm("Are you sure you want to delete this entry?");
        if(r){
            var thisRow = $(this);
            var victimID = $(this).data('victimid');
            var p = servicePaths.deleteVictim + 'victimID=' + victimID + '&callback=?';
            jQuery.getJSON(p, function (data){
                thisRow.slideUp(function(){
                    $(this).remove();
                });
            });
        }
    };
    $.fn.validateField =function(){
        var property = $(this).data('property');
        var value = $(this).val();
        var victim = $(this).parent().parent();
        if(property == 'date'){
            //try to recognize date and parse to javascript's date
            $(this).parent().find('.little').remove();
            if(value !== ''){
                var date;
                var today_time;
                var day;
                if(dayMappings[value.toLowerCase()] !== undefined){
                    today_time = new Date();
                    var today_day = today_time.getDay();
                    var then_day = dayMappings[value.toLowerCase()];
                    var daysPassed = then_day < today_day ? today_day - then_day : today_day + (7 - then_day);
                    var timePassed = daysPassed * 24 * 60 * 60 * 1000;
                    date = new Date(today_time - timePassed);
                }
                else if(value.toLowerCase() == 'today'){
                    date = new Date();
                }
                else if(value.toLowerCase() == 'yesterday'){
                    today_time = new Date();
                    date = new Date(today_time - 24 * 60 * 60 * 1000);
                }
                else{
                    date = parseDate(value);
                }
                if(date == 'Invalid Date'){
                    $(this).val('');
                }
                else{
                    var s = '';
                    var month = date.getMonth() + 1;
                    day = date.getDate();
                    var year = date.getFullYear();
                    s = month + '/' + day + '/' + year;
                    $(this).val(s);
                }
                day = weekday[date.getDay()];
                $(this)
                    .parent()
                    .append('<span class="little">' + day + '</span>');
            }
        }
        else if(property == 'age'){
            value = value.toLowerCase();
            if(value == 'adult' || value == 'child' || value == 'teen' || value >= 0 || value === ''){
                if(value == 'adult' || value == 'child' || value == 'teen'){
                    $(this).val('');
                }
                victim.find('.ageGroup')
                    .val(getAgeGroup(value))
                    .end()
                    .val(value);
            }
            else{
                $(this).val('');
            }
        }
        else if(property == 'gender'){
            value = value.toUpperCase();
            if(value == 'W'){
                $(this).val('F');
            }
            else if(value == 'F' || value == 'M'){
                $(this).val(value);
            }
            else{
                $(this).val('');
            }
        }
        else if(property == 'state'){
            if(value.length > 2){
                var abbrev = convert_state(value, 'abbrev');
                if(abbrev){
                    $(this).val(abbrev);
                }
                else{
                    $(this).val('');
                }
            }
            else{
                if(checkState(value, 'abbrev')){
                    $(this).val($(this).val().toUpperCase());
                }
                else{
                    $(this).val('');
                }
            }
        }
        else if(property == 'keyword'){
            var keys = value.split(' ');
            var keepKeys = [];
            for(var i = 0; i < keys.length; i++){
                if(processKey(keys[i])){
                    keepKeys.push(keys[i]);
                }
            }
            $(this).val(keepKeys.join(' '));
        }
        function processKey(key){
            keyLC = key.toLowerCase();
            if(key.indexOf('/') > -1 || dayMappings[key] !== undefined){
                victim.find('input.date')
                    .val(key)
                    .validateField()
                    .addToFilters(modeOpt[mode]);
            }
            else if(keyLC == 'w' || keyLC == 'f' || keyLC == 'm'){
                victim.find('input.gender')
                    .val(key)
                    .validateField()
                    .addToFilters(modeOpt[mode]);
            }
            else if(key > 0){
                victim.find('input.victimID')
                    .val(key)
                    .validateField()
                    .addToFilters(modeOpt[mode]);
            }
            else if(keyLC == 'adult' || keyLC == 'child' || keyLC == 'teen'){
                victim.find('input.ageGroup')
                    .val(keyLC)
                    .addToFilters(modeOpt[mode]);
            }
            else if(checkState(key, 'abbrev') !== false || checkState(key, 'name') !== false){
                victim.find('input.state')
                    .val(key)
                    .validateField()
                    .addToFilters(modeOpt[mode]);
            }
            else{
                return true;
            }
            return false;
        }

        if((property == 'city' || property == 'state')){
            if(value === ''){
                victim.find('input.lat').val('');
                victim.find('input.lng').val('');
            }
            else if(victim.find('input.city').val() !== '' && victim.find('input.state').val() !== ''){
                var city = victim.find('input.city').val();
                var state = victim.find('input.state').val();
                victim.find('.saveRow').add('#btn_add').addClass('disabled');
                victim.find('input.lat').val('thinking...');
                victim.find('input.lng').val('thinking...');
                getLatLng(city, state, function (lat, lng){
                    victim.find('input.lat').val(lat);
                    victim.find('input.lng').val(lng);
                    victim.find('.saveRow').add('#btn_add').removeClass('disabled');
                });
            }
        }
        return this;
    };
    $.fn.copyRow =function(){
        switchMode('add');
        $(this).find('input.date,input.city,input.state,input.lat,input.lng,input.url,input.note').each(function(){
            var property = $(this).data('property');
            $('#editBox').find('input.' + property)
                .val($(this).val());
            if(victimProperties[property]['addMatch']!==undefined == "true"){
                $(this)
                    .addToFilters(modeOpt[mode][$(this).data('property')]);
            }
        });
        getVictims();
    };
    $.fn.tweetRow =function(){
        $(this).saveToLastAdded();
        lastAdded["tweeted"] = $(this).find('.tweetRow').hasClass('tweeted');
        makeTweet();
        return this;
    };
    $.fn.saveToLastAdded =function(){
        lastAdded ={};
        $(this).find('input').each(function(){
            var property = $(this).data('property');
            var value = $(this).val();
            lastAdded[property] = value;
        });
        return this;
    };
    /*
    $.fn.markTweeted =function(){
        $(this).find('.tweetRow').addClass('tweeted');
    };
    */
    function getVictims(){
        $('#list').empty();
        $('#searchDescription').empty();
        $('#btn_nextPage,#btn_previousPage').hide();
        if(mode == 'search' || (mode == 'add' && (modeOpt[mode]['filters']['name'] !==undefined || modeOpt[mode]['filters']['city'] !== undefined || modeOpt[mode]['filters']['minDate'] !== undefined))){
            $('#list').append('<p id="loader"><img id="loaderImage" src="graphics/ajax-loader.gif"/>Fetching data</p>');
            buildSearchDescription();
            var p = servicePaths.getVictims +
                (modeOpt[mode]['filters'] !== undefined ? buildQueryString(modeOpt[mode]['filters']) : '') +
                (modeOpt[mode]['orderBy'] !== undefined ? '&orderBy=' + modeOpt[mode]['orderBy'] : '') +
                (modeOpt[mode]['sort'] !== undefined ? '&sort=' + modeOpt[mode]['sort'] : '') +
                (modeOpt[mode]['limitx'] !== undefined ? '&limitx=' + modeOpt[mode]['limitx'] : '0') +
                '&callback=?';
            console.log(p);
            $.ajax({
                dataType: "json",
                url: p,
                success: function (data){
                    $('#loader').remove();
                    if(data.length > 0){
                        printTableHeads();
                        var len = data.length;
                        for(var i = 0; i < len; i++){
                            printVictim(data[i]);
                        }
                        if(data.length == resultsPerPage){
                            $('#btn_nextPage').show();
                        }
                        else{
                            $('#btn_nextPage').hide();
                        }
                        if(modeOpt[mode]['limitx']!==0){
                            $('#btn_previousPage').show();
                        }
                        else{
                            $('#btn_previousPage').hide();
                        }
                    }
                    else{
                        $('#list').append('No results found');
                    }
                },
                error:function(){
                    alert('The server could not be reached. Please check your internet connection or contact chris.kirk@slate.com to report a server outage.');
                }
            });
        }

        function buildSearchDescription(){
            var D = modeOpt[mode]['filters'];
            var c = 0;
            var s = 'Showing all victims where ';
            var len = 0;
            for(var i in D){
                len++;
            }
            for(i in D){
                if(mode == 'search'){
                    s += '<span data-type="filters" data-property="' + i + '">';
                }
                s += '<strong>' + i + '</strong>' + '=' + D[i];
                s += '</span>';
                if(c != len - 1){
                    s += ',';
                }
                else{
                    s += ' and ';
                }
                c++;
            }
            if(c===0){
                s = '';
            }
            if(modeOpt[mode]['orderBy']===undefined){
                s += 'ordering by time last modified';
            }
            else{
                if(mode == 'search'){
                    s += '<span data-type="orderBy">';
                }
                s += 'ordering by ' + modeOpt[mode]['orderBy'];
                if(mode == 'search'){
                    s += '</span>';
                }
            }
            $('#searchDescription').html(s);
        }

        function buildQueryString(D){
            var s = '';
            for(var i in D){
                s += '&' + i + '=' + D[i];
            }
            return s;
        }
    }
    function printTableHeads(){
        var tableHeads = $('<div id="tableHeads">');
        for(var i in victimProperties){
            var display_name = i;
            if(victimProperties[i]['short'] !== undefined){
                display_name = victimProperties[i]['short'];
            }
            if(victimProperties[i]['inTable']!="false"){
                $('<div class="tableHead ' + i + '">' + display_name + '</div>')
                    .data('property', i)
                    .appendTo(tableHeads);
            }
        }
        tableHeads.appendTo('#list');
    }
    function printVictim(victimData){
        var victimBox = $('<div class="victim">').data('victimid', victimData['victimID']);
        $('<div class="editRow"><img src="graphics/pencil.png"/></div>').appendTo(victimBox);
        for(var i in victimProperties){
            if(victimProperties[i]['inTable']!="false"){
                var newCell = $('<div class="property ' + i + '">' + '<input type="text" disabled="disabled" ' + ' data-edit="' + victimProperties[i]['edit'] + '"' + ' data-property="' + i + '"' + ' class="' + i + '"' + ' value="' + decodeData(i, victimData[i]) + '"/>' + '</div>');
                if(i == 'date'){
                    var date = new Date(decodeData(i, victimData[i]));
                    var day = weekday[date.getDay()];
                    $('<span class="little">' + day + '</span>').appendTo(newCell);
                }
                if(i=='url'&&victimData.broken==='1'){
                    newCell.find('input').css('color','red');
                }
                newCell.appendTo(victimBox);
            }
        }
        victimBox
            .append('<div class="deleteRow"></div>')
            .append('<div class="copyRow"></div>')
            .append('<div class="historyRow"></div>')
            .appendTo('#list');
        //var tweetRow = $('<div class="tweetRow"></div>');
        /*
        if(victimData['tweeted']==1){
            tweetRow.addClass('tweeted');
        }*/
        //tweetRow.appendTo(victimBox);
    }
    function decodeData(property, value){
        if(value===null){
            return '';
        }
        if(property == 'ageGroup'){
            return value == 3 ? 'adult' : value == 2 ? 'teen' : value == 1 ? 'child' : '';
        }
        else if(property == 'date'){
            value = value.split('-');
            var dateString = parseInt(value[1],10) + '/' + parseInt(value[2],10) + '/' + value[0];
            return dateString;
        }
        else if(property=='url'){
            return decodeURIComponent(value);
        }
        return value;
    }
    function getLatLng(city, state, callback){
        if(state.length < 3){
            state = convert_state(state, "name");
        }
        var p;
        if(state == "D.C."){
            p = "http://nominatim.openstreetmap.org/search?format=json&q=" + city + " " + state + ',USA' + "&email=chris.kirk@slate.com&json_callback=?";
        }
        else{
            p = "http://nominatim.openstreetmap.org/search?format=json&q=" + city + ',' + state + ',USA' + "&email=chris.kirk@slate.com&json_callback=?";
        }
        $.ajax({
            url: p,
            dataType: 'jsonp',
            success: function (data){
                if(data.length > 0){
                    var lat = data[0]['lat'];
                    var lng = data[0]['lon'];
                    return callback(lat, lng);
                }
                return callback('','');
            },
            error: function (xhr, ajaxOptions, thrownError){
                alert('Locations are not loading. Please try again later');
                //alert(xhr.status);
                //alert(thrownError);
            }
        });
    }
    function clearParameters(){
        $('#editBox').find('input').val('');
    }
    function addVictim(){
        var victim = $('#editBox');

        function checkRequiredFields(){
            var output = true;
            victim.find('.required')
                .each(function(){
                if($(this).val()===''){
                    output = false;
                    return false;
                }
                return true;
            });
            return output;
        }
        if(checkRequiredFields()){
            var p = servicePaths.addVictim + '&' + getQueryString() + '&userID=' + userID + '&callback=?';
            console.log(p);
            $.ajax({
                url: p,
                dataType: 'json',
                success: function (data){
                    getVictims();
                    victim.saveToLastAdded();
                    /*if(data["tweeted"]){
                        lastAdded["tweeted"]=true;
                    }*/
                    makeTweet();
                    victim.find('input')
                        .focus(function(){
                        modeOpt[mode]['filters'] ={};
                        $('#tweetBox').hide();
                        $('#editBox input').unbind('focus');
                    })
                        .val('');
                },
                error:function(xhr,ajaxOptions,thrownError){
                    console.log(xhr);
                    console.log(ajaxOptions);
                    console.log(thrownError);
                    alert('Error. Database did not update.');
                }
            });
        }
        else{
            alert('You are missing a required field');
        }
        function getQueryString(){
            var data = [];
            victim.find('input').each(function(){
                var type = $(this).attr('type');
                var property = $(this).data('property');
                var val;
                if(type=='checkbox'){
                    val = $(this).is(':checked');
                }
                else{
                    val = $(this).val();
                }
                if(property=='url'){
                    val = encodeURIComponent(val);
                }
                data.push(property + '=' + val);
            });
            var s = data.join('&');
            return s;
        }
    }
    function makeTweet(){
        var victim = lastAdded;
        $('#tweetBox textarea').val('');
        $('#tweetBox').slideDown();
        var fragments = '';
        var headline = $('#tweetBox').find('input.headline').val();
        var tweeter = $('#tweetBox').find('input.tweeter').val();
        var date = victim['date'];
        date = new Date(date);
        date = (date.getMonth() + 1) + '/' + (date.getDate());

        if(headline!==''){
            fragments = headline;
        }
        else{
            var fragment_age = getAge();
            var fragment_noun = '';
            if(fragment_age===''){
                fragment_noun = capitaliseFirstLetter(getNoun());
            }
            else if(fragment_age != 'Newborn'){
                fragment_noun = getNoun();
            }
            tweet_url = victim['url'];
            fragments = fragment_age + fragment_noun + ' killed';
        }
        var s = date + ': ' + victim['city'] + ', ' + victim['state'] + ': ' + fragments;
        if(tweeter!==''){
            tweet_via = tweeter.replace('@','');
            if(tweeter[0] != '@'){
                tweeter = '@' + tweeter;
            }
            tweeter = ' via ' + tweeter;
        }
        else{
            tweet_via = undefined;
        }

        tweet_text = s;
        $('#tweetBox textarea').val(s + ' ' + tweet_url + tweeter);
        if(victim['tweeted']=="true"){
            $('#alreadyTweeted').show();
        }
        else{
            $('#alreadyTweeted').hide();
        }
        //noun is either the proper name of the victim, or a more generic term like "man" or "woman"
        function getNoun(){

            //if gender defined
            if(victim['gender']!==undefined){
                if(victim['gender'] == 'F'){
                    if(victim['ageGroup'] == 'teen' || victim['ageGroup'] == 'child'){
                        return 'girl';
                    }
                    else if(victim['ageGroup'] == 'adult'){
                        return 'woman';
                    }
                }
                else if(victim['gender'] == 'M'){
                    if(victim['ageGroup'] == 'teen' || victim['ageGroup'] == 'child'){
                        return 'boy';
                    }
                    else if(victim['ageGroup'] == 'adult'){
                        return 'man';
                    }
                }
            }
            //if gender is not defined, but age group is
            else if(victim['ageGroup']!==undefined){
                if(victim['ageGroup'] == 'child'){
                    return 'child';
                }
                else if(victim['ageGroup'] == 'teen'){
                    return 'teen';
                }
            }
            return 'person';
        }
        function getAge(){
            if(victim['age']!==undefined){
                if(victim['age'] > 1){
                    return victim['age'] + '-year-old ';
                }
                else if(victim['age']!=='' && victim['age']===0){
                    return 'Newborn';
                }
            }
            return '';
        }
        function capitaliseFirstLetter(string){
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    }
    $('#tweetshare').click(function(){
        var width = 575,
            height = 400,
            left = ($(window).width() - width) / 2,
            top = ($(window).height() - height) / 2,
            opts = 'status=1' +
                ',width=' + width +
                ',height=' + height +
                ',top=' + top +
                ',left=' + left;
        var URL = 'http://twitter.com/intent/tweet?' + '&text=' + tweet_text + '&url=' + encodeURIComponent(tweet_url);
        if(tweet_via!==undefined){
            URL += '&via=' + tweet_via;
        }
        window.open(URL, 'twitter', opts);
        /*
        var p = servicePaths.tweetVictim + 'url=' + lastAdded['url'];
        $.ajax({
            url: p,
            dataType: "json",
            success: function (data){
                if(lastAdded['tweeted']!=1){
                    $('.victim').each(function(){
                        if($(this).find('input.url').val()==lastAdded['url']){
                            $(this).markTweeted();
                        }
                    });
                }
            },
            error:function(){
                alert('Error. Database did not update');
            }
        });*/
    });
});

//state validation
var states = [{
    'name': 'Alabama',
    'abbrev': 'AL'
},{
    'name': 'Alaska',
    'abbrev': 'AK'
},{
    'name': 'Arizona',
    'abbrev': 'AZ'
},{
    'name': 'Arkansas',
    'abbrev': 'AR'
},{
    'name': 'California',
    'abbrev': 'CA'
},{
    'name': 'Colorado',
    'abbrev': 'CO'
},{
    'name': 'Connecticut',
    'abbrev': 'CT'
},{
    'name': 'Delaware',
    'abbrev': 'DE'
},{
    'name': 'Florida',
    'abbrev': 'FL'
},{
    'name': 'Georgia',
    'abbrev': 'GA'
},{
    'name': 'Hawaii',
    'abbrev': 'HI'
},{
    'name': 'Idaho',
    'abbrev': 'ID'
},{
    'name': 'Illinois',
    'abbrev': 'IL'
},{
    'name': 'Indiana',
    'abbrev': 'IN'
},{
    'name': 'Iowa',
    'abbrev': 'IA'
},{
    'name': 'Kansas',
    'abbrev': 'KS'
},{
    'name': 'Kentucky',
    'abbrev': 'KY'
},{
    'name': 'Louisiana',
    'abbrev': 'LA'
},{
    'name': 'Maine',
    'abbrev': 'ME'
},{
    'name': 'Maryland',
    'abbrev': 'MD'
},{
    'name': 'Massachusetts',
    'abbrev': 'MA'
},{
    'name': 'Michigan',
    'abbrev': 'MI'
},{
    'name': 'Minnesota',
    'abbrev': 'MN'
},{
    'name': 'Mississippi',
    'abbrev': 'MS'
},{
    'name': 'Missouri',
    'abbrev': 'MO'
},{
    'name': 'Montana',
    'abbrev': 'MT'
},{
    'name': 'Nebraska',
    'abbrev': 'NE'
},{
    'name': 'Nevada',
    'abbrev': 'NV'
},{
    'name': 'New Hampshire',
    'abbrev': 'NH'
},{
    'name': 'New Jersey',
    'abbrev': 'NJ'
},{
    'name': 'New Mexico',
    'abbrev': 'NM'
},{
    'name': 'New York',
    'abbrev': 'NY'
},{
    'name': 'North Carolina',
    'abbrev': 'NC'
},{
    'name': 'North Dakota',
    'abbrev': 'ND'
},{
    'name': 'Ohio',
    'abbrev': 'OH'
},{
    'name': 'Oklahoma',
    'abbrev': 'OK'
},{
    'name': 'Oregon',
    'abbrev': 'OR'
},{
    'name': 'Pennsylvania',
    'abbrev': 'PA'
},{
    'name': 'Rhode Island',
    'abbrev': 'RI'
},{
    'name': 'South Carolina',
    'abbrev': 'SC'
},{
    'name': 'South Dakota',
    'abbrev': 'SD'
},{
    'name': 'Tennessee',
    'abbrev': 'TN'
},{
    'name': 'Texas',
    'abbrev': 'TX'
},{
    'name': 'Utah',
    'abbrev': 'UT'
},{
    'name': 'Vermont',
    'abbrev': 'VT'
},{
    'name': 'Virginia',
    'abbrev': 'VA'
},{
    'name': 'Washington',
    'abbrev': 'WA'
},{
    'name': 'West Virginia',
    'abbrev': 'WV'
},{
    'name': 'Wisconsin',
    'abbrev': 'WI'
},{
    'name': 'Wyoming',
    'abbrev': 'WY'
},{
    'name': 'D.C.',
    'abbrev': 'DC'
},{
    'name': 'District of Columbia',
    'abbrev': 'DC'
}];

function checkState(value, type){
    for(var state in states){
        if(value.toLowerCase() == states[state][type].toLowerCase()){
            return true;
        }
    }
    return false;
}

function convert_state(name, to){
    var output = false;
    for(var state in states){
        if(to == 'name'){
            if(states[state]['abbrev'].toLowerCase() == name.toLowerCase()){
                output = states[state]['name'];
                break;
            }
        }
        else if(to == 'abbrev'){
            if(states[state]['name'].toLowerCase() == name.toLowerCase()){
                output = states[state]['abbrev'].toUpperCase();
                break;
            }
        }
    }
    return output;
}

//date validation
var weekday = new Array(7);
weekday[0] = "sun";
weekday[1] = "mon";
weekday[2] = "tue";
weekday[3] = "wed";
weekday[4] = "thu";
weekday[5] = "fri";
weekday[6] = "sat";

var dayMappings ={
    'sunday': 0,
    'sun': 0,
    'monday': 1,
    'mon': 1,
    'tuesday': 2,
    'tue': 2,
    'tues': 2,
    'wednesday': 3,
    'wed': 3,
    'thursday': 4,
    'thu': 4,
    'thurs': 4,
    'friday': 5,
    'fri': 5,
    'saturday': 6,
    'sat': 6
};

    function parseDate(input){
        //input: a string, MM/DD/YYYY or MM/DD/YY. returns a date.
        var parts = input.match(/(\d+)/g);
        if(parts!==null&&parts.length > 0){
            var today;
            if(parts.length == 1){ //if month is specified but nothing else
                today = new Date();
                var todayDate = today.getDate();
                var month;
                if(parts[0] <= todayDate){ //date is in same month
                    month = today.getMonth() + 1;
                }
                else if(parts[0] > todayDate){ //date is in last month
                    month = today.getMonth();
                }
                parts = [month, parts[0], today.getFullYear()];
            }
            else if(parts.length == 2){ //if month and date are specified but not year
                today = new Date();
                var todayMonth = today.getMonth();
                var year;
                if(parts[0] - 1 <= todayMonth){ //same month or less, so this year
                    year = today.getFullYear();
                }
                else if(parts[0] - 1 > todayMonth){ //greater than this month, so last year
                    year = today.getFullYear() - 1;
                }
                parts.push(year);
            }
            else if(parts[2].length == 2){ //if year is specified but the millenium is not specified
                parts[2] = '20' + parts[2];
            }
            var output = new Date(parts[2], parts[0] - 1, parts[1]);
            if(output == 'Invalid Date'){
                output = new Date(Date.parse(input));
            }
            return output;
        }
        return input;
    }