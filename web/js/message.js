$(function(){
	page
	.on('sectioncreate','section[hash="message"]',function(){
		$(this).on('click','.message-dialog-list-item',function(){
			syssh.navigate('message/content/'+$(this).attr('id'),true);
		});

		$(this).on('mouseenter','.message-dialog-list-item',function(){
			$(this).children('#delete').show()
				.on('click.deletedialogmessage',function(event){
					event.stopPropagation();
					var id=$(this).parent('.message-dialog-list-item').attr('id');
					$.post('/message/deletedialogmessage/'+id,function(){
						$.refresh(hash);
					});
				});
		});
		$(this).on('mouseleave','.message-dialog-list-item',function(){
			$(this).children('#delete').hide().off('.deletedialogmessage');
		});
	})
	.on('sectioncreate','section[hash^="message/content"]',function(){
		window.clearInterval(polling.message);
		polling.message=window.setInterval(function(){
			$.get('/'+hash,{blocks:'content'});
		},3000);
		
		$(this).on('mouseenter','.message-content-list-item',function(){
			$(this).children('#delete').show()
				.on('click.deletemessage',function(){
					var id=$(this).parent('.message-content-list-item').attr('id');
					$.post('/message/delete/'+id,function(){
						$.get('/'+hash,{blocks:'content'});
					});
				});
		});
		$(this).on('mouseleave','.message-content-list-item',function(){
			$(this).children('#delete').hide().off('.deletemessage');
		});
	});
});