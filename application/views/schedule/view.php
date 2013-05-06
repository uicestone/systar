<div class="field"><?=str_replace("\n", '<br>', $this->value('schedule/content'))?></div>
<div class="field" style="border:0;">
<?if($this->value('schedule/project')){?>
	事项：<?=$this->value('project/name')?> 
<?}?>
<?if($this->value('schedule/uid')!=$this->user->id){?>
	<br />
	创建人：<?=$this->value('schedule/creater_name')?>
<?}?>
<?if($this->value('schedule/deadline')){?>
	<br />
	截止：<?=$this->value('schedule/deadline')?>
<?}?>
</div>
<?foreach($profiles as $profile){?>
<div class="field"><?=$profile['name']?>: <?=$profile['content']?></div>
<?}?>
<div class="profile hidden">
	<select class="profile-name allow-new" style="width:35%">
		<?=options(array('外出地点','费用金额','费用用途','备注'),NULL)?>
	</select>
	: 
	<input type="text" name="profiles[]" placeholder="信息内容" style="width:60%" />
</div>