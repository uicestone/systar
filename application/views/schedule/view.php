<div class="field"><?=str_replace("\n", '<br>', $this->value('schedule/content'))?></div>
<div class="field" style="border:0;"><?=$this->value('project/name')?></div>
<?foreach($profiles as $profile){?>
<div class="field"><?=$profile['name']?>: <?=$profile['content']?></div>
<?}?>
<div class="profile hidden">
	<select class="profile-name" style="width:23%">
		<?=options(array('外出地点','费用金额','费用用途','备注'),NULL)?>
	</select>
	: 
	<input type="text" name="profiles[]" placeholder="信息内容" style="width:68%" />
</div>