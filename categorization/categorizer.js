$(function(){
	$('.lower').fadeIn(500);
	function fullWidthElement(conf){
		function sizeEle(){
			conf.ele
				.css({
					'width':$(window).width(),
					'position':'static'
				});
			if(conf.stretchHeight){
				conf.ele.css({'height':$(window).height()*0.9});
			}
			conf.ele
				.css({
					'position':'relative',
					'left':conf.ele.offset().left*-1
				});
			if(conf.callback){conf.callback();}
		}
		$(window).resize(function(){
			sizeEle();
		});
		sizeEle();
	}
	fullWidthElement({ele:$('#interactive')});

});