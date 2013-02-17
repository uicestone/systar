(function($){
	hashchangeSupport=('onhashchange' in window) && ((typeof document.documentMode==='undefined') || document.documentMode>=8);

	$.locationHash=function(hash){
		if(!hash){
			return window.location.hash.substr(1);
		}
		else{
			window.location.hash='#'+hash;
			if(!hashchangeSupport){
				$(window).trigger('hashchange');
			}
		}
	}
	
	if(!hashchangeSupport){
		$(document).on('click','a[href^="#"]',function(){
			if($(this).attr('href').substr(1)!=hash){
				$(window).trigger('hashchange');
			}
		});
	}
	
})(jQuery)