$(function(){
	
	var section=page.children('section[hash="'+hash+'"]');
	var side=aside.children('section[for="'+hash+'"]');
	
	/*日程excel导出按钮*/
	section.find('[name="export-excel"]').click(function(){
		window.open(changeURLPar(hash,'export','excel'));
	});
	
	$.each([section,side],function(){
		this.find('.portlet')
			.on('click',function(){
				var event={id:$(this).attr('id')};
				$.viewSchedule({id:event.id,selection:this});
			});
		
		this.find('.portlet-header .ui-icon')
			.on('click',function(event){
				event.stopPropagation();
				$( this ).toggleClass( 'ui-icon-minusthick' ).toggleClass( 'ui-icon-plusthick' );
				$( this ).parents( '.portlet:first' ).find( '.portlet-content' ).toggle();
			});
	});

});