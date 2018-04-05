<form method="post" name="<?=CONTROLLER?>" id="<?=$this->team->id?>">
	<div class="item">
		<div class="title"><label>基本信息：</label></div>
		<label>名称：</label><input name="people[name]" value="<?=$this->value('people/name'); ?>" type="text" placeholder="名称" />
		<label>组长：</label><?php if($this->value('people/leader')){ ?><?=$this->people->fetch($this->value('people/leader'),'name')?><?php } ?>
	</div>

	<div class="item" name="relative">
		<div class="title"><label>成员</label></div>
		<?=$relative_list?>
<?php if(true ||$this->user->id==$this->value('people/leader')){ ?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="hidden" name="relative[id]" class="tagging" data-ajax="/people/match/" value="<?=$this->value('relative/name')?>" placeholder="姓名" />
			<input type="text" name="relative[relation]" placeholder="关系" />
			<button type="submit" name="submit[relative]">添加</button>
		</span>
<?php } ?>
	 </div>

	<div class="item">
		<div class="title"><label>相关事务</label></div>
		<?=$project_list?>
	 </div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea name="people[comment]"><?=$this->value('people/comment')?></textarea>
	</div>
</form>
<?=$this->javascript('people_add')?>