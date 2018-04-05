<div class="field"><?=str_replace("\n", '<br>', $this->value('schedule/content'))?></div>
<div class="field" style="border:0;">
<?php if($this->value('schedule/project')){ ?>
	事项：<?=$this->value('project/name')?> 
<?php } ?>
<?php if($this->value('schedule/uid')!=$this->user->id){ ?>
	创建人：<?=$this->value('schedule/creater_name')?>
<?php } ?>
<?php if($this->value('schedule/deadline')){ ?>
	<br />
	截止：<?=$this->value('schedule/deadline')?>
<?php } ?>
</div>
<?foreach($profiles as $profile){ ?>
<div class="field profile" id="<?=$profile['id']?>" style="border-bottom: none;border-top:#999 1px solid;">
	<?=$profile['name']?>：<?=$profile['content']?> (<?=$profile['author_name']?>)
	<?php if($profile['author']==$this->user->id){ ?><button id="remove" class="hidden" style="position:absolute;">-</button><?php } ?>
</div>
<?php } ?>
<div class="profile hidden" style="text-align:left;">
	<hr />
	<select class="profile-name allow-new" style="width:98%">
		<?=options(array('外出地点','费用金额','费用用途','备注'),NULL)?>
	</select>
	<br />
	<input type="text" name="profiles[]" style="width:98%" />
</div>