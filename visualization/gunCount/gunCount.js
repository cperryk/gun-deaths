$(function(){
	var deaths_in_2012 = 36570;
	var days_in_2012 = 366;
	var average_deaths_per_day_in_2012 = deaths_in_2012/days_in_2012;
	var now = new Date();
	var today = new Date(now.getFullYear(),now.getMonth(),now.getDate());
	var Newtown_date = new Date(2012,12,14);
	var days_since_Newtown = Math.round((today - Newtown_date)/1000/60/60/24);
	var deaths_since_Newtown = Math.round(average_deaths_per_day_in_2012*days_since_Newtown);
	var dateString = (today.getMonth()+1) + '/' + today.getDate() + '/' + today.getFullYear();
	$('.count_here').html(commaSeparateNumber(deaths_since_Newtown));
	$('.date_here').html(dateString);
	function commaSeparateNumber(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
});