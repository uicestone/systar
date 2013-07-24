<form method="post" controller="email" method="send_express" enctype="multipart/form-data" encoding="multipart/form-data">
	<div class="contentTableMenu">
		<button type="submit" name="submit[generate_express]">生成</button>
		<button type="submit" name="download-express">下载</button>
		<button type="submit" name="submit[send_express]">发送</button>
	</div>
	<div class="item"><div class="title">邮件列表</div>
		<textarea name="client-emails"><?//=implode(', ',$client_emails)?></textarea>
	</div>

	<div class="item"><div class="title">发送状态</div>
		<div id="delivery-status"></div>
	</div>

	<div class="item"><div class="title">期刊信息</div>
		<input type="text" name="title" placeholder="标题" />
		<input type="text" name="articles" placeholder="文章ids（半角逗号分隔）" style="width:300px;" />
	</div>

	<div class="item"><div class="title">题头图</div>
		<input type="file" name="header" />
	</div>

	<div class="item"><div class="title">附件</div>
		<input type="file" name="attachment" />
	</div>

	<div class="item"><div class="title">预览</div>
		<div id="express-preview"></div>
	</div>
</form>
<?=$this->javascript('jQuery/jquery.form');?>
<script type="text/javascript">
	$(function(){
		$('[name="submit[generate_express]"]').on('click',function(event){
			event.stopPropagation();
			$('form[controller="email"]').ajaxForm({
				url:'/mail/submit/generate_express',
				dataType:'json',
				success:function(response){
					$(document.body).setBlock(response);
				}
			});
		});
		
		$('[name="download-express"]').click(function(){
			window.open('/mail/submit/download');
		});
	});
</script>