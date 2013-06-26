<button type="submit" name="submit[document]" class="major">保存</button>
<select name="labels[]" class="chosen allow-new" data-placeholder="标签" multiple="multiple">
	<?=options($this->document->getAllLabels(),$labels)?>
</select>
<?if($mod&2){?>
<button type="submit" name="submit[delete]" class="major">删除</button>
<?}?>
<?if($mod&3){?>
<label>有权限查看：</label>
<select name="read_mod_people" class="chosen" data-placeholder="查看权限" multiple="multiple" title="查看权限">
	<?=options($this->user->teams
			+$this->user->getArray(array('is_relative_of'=>array_keys($this->user->teams)+array($this->user->id)),'name','id')
			+$this->user->getArray(array('has_relative_like'=>$this->user->id),'name','id')
			+$this->user->getArray(array('is_secondary_relative_of'=>$this->user->id),'name','id')
			+$this->user->getArray(array('is_both_relative_with'=>$this->user->id),'name','id')
		,$read_mod_people,NULL,true)?>
</select>
<?}?>
<?=$this->javascript('document_edit')?>
