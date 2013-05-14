<form method="post" name="<?=CONTROLLER?>" id="<?=$this->people->id?>" enctype="multipart/form-data">
	<div class="item">
		<div class="title"><label>基本信息：</label></div>
		<input name="people[name]" value="<?=$this->value('people/name'); ?>" type="text" placeholder="姓名" />

		<select name="people[type]">
			<?=options($this->people->getTypes(),$this->value('people/type'),'人员类型',true)?>
		</select>

		<select name="people[gender]"><?=options(array('男','女'), $this->value('people/gender'), '性别')?></select>
		<input type="text" name="people[id_card]" value="<?=$this->value('people/id_card'); ?>" placeholder="身份证" style="width:195px;" />
		<input type="text" name="people[birthday]" value="<?=$this->value('people/birthday'); ?>" placeholder="生日" class="date" />
	</div>

	<div class="item" name="score">
		<div class="title">
			<label class="right"><a href="#student/viewscore/<?=$this->value('people/id')?>">查看全部</a></label>
			<label>成绩</label>
		</div>
		<?=$score_list?>
	 </div>

	<div class="item" name="class">
		<div class="title">
			<label class="right">班主任：<?=$class['leader_name']?>
				<a href="#message/to/<?=$class['leader']?>"><img src="images/message.png"></a>
			</label>
			<label><a href="#classes/<?=$class['id']?>"><?=$class['name']?></a></label>
		</div>
		
	 </div>

	<div class="item" name="status">
		<div class="title"><label>动态</label></div>
		<?=$status_list?>
<?if($this->user->inTeam('教师')){?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="status[name]" value="<?=$this->value('status/name')?>" placeholder="状态" />
			<input type="text" name="status[date]" value="<?=$this->value('status/date')?>" class="date" placeholder="日期" />
			<input type="text" name="status[content]" value="<?=$this->value('status/content')?>" placeholder="内容" />
			<input type="text" name="status[comment]" value="<?=$this->value('status/comment')?>" placeholder="备注" />
			<select name="status[team]">
				<?=options($this->user->teams,$this->value('status/team'),'评价团队',true)?>
			</select>
			<button type="submit" name="submit[status]">添加</button>
		</span>
<?}?>
	 </div>

	<div class="item" name="profile">
		<div class="title"><label>资料项</label></div>
		<?=$profile_list?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<select name="profile[name]">
				<?=options($profile_name_options,$this->value('profile/name'),'资料项名称')?>
			</select>
			<input type="text" name="profile[content]" value="<?=$this->value('profile/content')?>" placeholder="资料项内容" />
			<input type="text" name="profile[comment]" value="<?=$this->value('profile/comment')?>" placeholder="备注" />

			<button type="submit" name="submit[profile]">添加</button>
		</span>
	 </div>

	<div class="item" name="relative">
		<div class="title"><label>相关人</label></div>
		<?=$relative_list?>
		<button type="button" class="toggle-add-form">＋</button>
		<span class="add-form hidden">
			<input type="text" name="relative[name]" value="<?=$this->value('relative/name')?>" placeholder="名称" autocomplete-model="people" />
			<input name="relative[id]" class="hidden" />

			<select name="relative[relation]">
				<?=options($this->config->item(($this->value('people/character')=='单位'?'单位':'个人').'相关人关系'),$this->value('relative/relation'),'关系',false,true)?>
			</select>
			<span display-for="new" class="hidden">
				<?=checkbox('单位','relative[character]',$this->value('relative/character'),'单位')?>

				<input type="text" name="relative_profiles[电话]" value="<?=$this->value('relative_profiles/电话')?>" placeholder="电话" />
				<input type="text" name="relative_profiles[电子邮件]" value="<?=$this->value('relative_profiles/电子邮件')?>" placeholder="电子邮件" />
			</span>
			<button type="submit" name="submit[relative]">添加</button>
		</span>
	 </div>

	<div class="item">
		<div class="title"><label>备注：</label></div>
		<textarea name="people[comment]"><?=$this->value('people/comment')?></textarea>
	</div>
</form>
<?=$this->javascript('people_add')?>