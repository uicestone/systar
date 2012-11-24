$(function(){
	$('iframe').attr('height',$(window).height()+'px');
	$('#contentFrame').attr('width',$(window).width()-120+'px');

	$(window).resize(function(){
		$('iframe').attr('height',$(window).height()+'px');
		$('#contentFrame').attr('width',$(window).width()-120+'px');
	});
	
	//无hash，载入默认content
	if(window.location.hash){
		$('#contentFrame').attr('src',window.location.hash.substr(1));
	}
});