<script type="text/javascript">
$(function(){
	$(':input[name="people[id_card]"]').on('blur',function(){
		/*根据身份证生成生日*/
		if($(this).val().length===18){
			$('input[name="people[birthday]"]').val($(this).val().substr(6,4)+'-'+$(this).val().substr(10,2)+'-'+$(this).val().substr(12,2)).trigger('change');
			if($(this).val().substr(16,1) % 2 === 0){
				$(':input[name="people[gender]"]').val('女').trigger('change');
			}else{
				$(':input[name="people[gender]"]').val('男').trigger('change');
			}
		}
	});
});
</script>
<form id="<?=$this->user->id?>">
	<div class="contentTableBox">
		<!--<div class="item">
			<div class="title"><label title="留空则不修改">设置用户名密码</label></div>
			<input type="text" name="user[username]" value="<?=$this->value('user/username')?>" placeholder="用户名" title="用户名" />
			<input type="password" name="user[password]" placeholder="密码" title="密码" />
			<input type="password" name="user[password_new]" placeholder="新密码" title="新密码" />
			<input type="password" name="user[password_new_confirm]" placeholder="新密码确认" title="新密码确认" />
		</div>-->
		<div class="item">
			<div class="title"><label>基本资料</label></div>
			<input type="text" name="people[name]" value="<?=$this->value('people/name')?>" placeholder="姓名" title="姓名" />
			<input type="text" name="people[id_card]" value="<?=$this->value('people/id_card')?>" placeholder="身份证号" title="身份证号" />
			<select name="people[gender]">
				<?=options(array('男','女'),$this->value('people/gender'),'性别')?>
			</select>
			<input type="text" name="people[birthday]" value="<?=$this->value('people/birthday')?$this->value('people/birthday'):'1998-01-01'?>" class="birthday" placeholder="生日" title="生日" />
		</div>
		<div class="item">
			<div class="title"><label>学生扩展资料</label></div>
			<input type="text" name="people_profiles[就读初中]" value="<?=$this->value('people_profiles/就读初中')?>" placeholder="就读初中" title="就读初中" />
			<label>团员：<?=radio(array('是','否'), 'people_profiles[是否团员]', $this->value('people_profiles/是否团员'))?></label>
			<input type="text" name="people_profiles[联系地址]" value="<?=$this->value('people_profiles/联系地址')?>" placeholder="联系地址" title="联系地址" />
			<input type="text" name="people_profiles[邮政编码]" value="<?=$this->value('people_profiles/邮政编码')?>" placeholder="邮政编码" title="邮政编码" />
			<input type="text" name="people_profiles[手机]" value="<?=$this->value('people_profiles/手机')?>" placeholder="手机" title="手机" />
			<input type="text" name="people_profiles[QQ]" value="<?=$this->value('people_profiles/QQ')?>" placeholder="QQ" title="QQ" />
			<input type="text" name="people_profiles[家庭电话]" value="<?=$this->value('people_profiles/家庭电话')?>" placeholder="家庭电话" title="家庭电话" />
			<input type="text" name="people_profiles[担任社会工作]" value="<?=$this->value('people_profiles/担任社会工作')?>" placeholder="担任社会工作" title="担任社会工作" />
			<input type="text" name="people_profiles[Email]" value="<?=$this->value('people_profiles/Email')?>" placeholder="Email" title="Email" />
			<label>户籍情况：<?=radio(array('本区','外区','外省市'), 'people_profiles[户籍情况]', $this->value('people_profiles/户籍情况'))?></label>
		</div>
		<div class="item">
			<div class="title"><label>报考意向</label></div>
			<select name="people_profiles[报考类别]">
				<?=options(array('区推荐生','市推荐生','自荐生','零志愿','中考统招'), $this->value('people_profiles/报考类别'), '报考类别', false, true)?>
			</select>
			<select name="people_profiles[报考途径]">
				<?=options(array('创新实验班','理科班','平行班'), $this->value('people_profiles/报考途径'), '报考途径')?>
			</select>
		</div>
		<div class="item">
			<div class="title"><label>区质管考成绩</label></div>
			<input type="text" name="people_profiles[区质管考语文成绩]" value="<?=$this->value('people_profiles/区质管考语文成绩')?>" placeholder="语文" title="语文" />
			<input type="text" name="people_profiles[区质管考数学成绩]" value="<?=$this->value('people_profiles/区质管考数学成绩')?>" placeholder="数学" title="数学" />
			<input type="text" name="people_profiles[区质管考英语成绩]" value="<?=$this->value('people_profiles/区质管考英语成绩')?>" placeholder="英语" title="英语" />
			<input type="text" name="people_profiles[区质管考理化成绩]" value="<?=$this->value('people_profiles/区质管考理化成绩')?>" placeholder="理化" title="理化" />
		</div>
		<div class="item">
			<div class="title"><label>自我介绍</label></div>
			<textarea name="people_profiles[自我介绍]" title="个性特长，获奖情况等" rows="8"><?=$this->value('people_profiles/自我介绍')?></textarea>
		</div>
	</div>
</form>