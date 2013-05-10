<form method="post" name="<?=CONTROLLER?>" id="<?=$this->document->id?>" enctype="multipart/form-data" encoding="multipart/form-data">
	<div class="item">
		<input type="text" name="document[name]" value="<?=$this->value('document/name')?>" placeholder="文档名称" class='large-field'>
	 </div>

	<div class="item">
		<div class="title"><label>文件：</label></div>
		<dl class="horizontal">
			<dt title="点击下载文件">
				<a href="/document/download/<?=$this->value('document/id')?>"><img src="/images/file_type/<?=$this->value('document/icon')?>" /></a>
			</dt>
			<table>
				<tr><th>文件名：</th><td><?=$this->value('document/filename')?></td></tr>
				<tr><th>大小：</th><td><?=$this->value('document/size')?>KB</td></tr>
				<tr><th>上传时间：</th><td><?=date('Y-m-d H:i',$this->value('document/time_insert'))?></td></tr>
				<tr><th>上传人：</th><td><?=$this->value('document/uploader_name')?></td></tr>
			</table>
		</dl>
	</div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea class="item" name="document[comment]" type="text" rows="3"><?=$this->value('document/comment')?></textarea>
	</div>
</form>