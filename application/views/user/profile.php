<script type="text/javascript">
$(function(){
	
	var section = page.children('[hash="'+hash+'"]');
	
	section.find(':input[name="people[id_card]"]').on('blur',function(){
		/*根据身份证生成生日*/
		if($(this).val().length===18){
			$('input[name="people[birthday]"]').val($(this).val().substr(6,4)+'-'+$(this).val().substr(10,2)+'-'+$(this).val().substr(12,2)).trigger('change');
			if($(this).val().substr(16,1) % 2 === 0){
				section.find(':input[name="people[gender]"]').val('女').trigger('change');
			}else{
				section.find(':input[name="people[gender]"]').val('男').trigger('change');
			}
		}
	});
	
});
</script>
<form id="<?=$this->user->id?>">
	<div class="item">
		<div class="title"><label>设置用户名密码</label></div>
		<input type="text" name="username" value="<?=$this->user->name?>" placeholder="用户名" title="用户名" />
		<input type="password" name="password" placeholder="当前密码" title="当前密码" />
		<input type="password" name="password_new" placeholder="新密码" title="新密码" />
		<input type="password" name="password_new_confirm" placeholder="新密码确认" title="新密码确认" />
		<label>密码留空则不修改</label>
	</div>
	<div class="item">
		<div class="title"><label>基本资料</label></div>
		<input type="text" name="people[name]" value="<?=$this->value('people/name')?>" placeholder="姓名" title="姓名" />
		<input type="text" name="people[id_card]" value="<?=$this->value('people/id_card')?>" placeholder="身份证号" title="身份证号" />
		<select name="people[gender]">
			<?=options(array('男','女'),$this->value('people/gender'),'性别')?>
		</select>
		<input type="text" name="people[birthday]" value="<?=$this->value('people/birthday')?>" class="birthday" placeholder="生日" title="生日" />
	</div>
<?php if($this->user->isLogged('student')){ ?>
	<div class="item">
		<div class="title"><label>学籍信息</label></div>
		 <?=checkbox('是否团员', 'profiles[是否团员]', $this->value('profiles/是否团员'), '是')?>
		 <?=checkbox('是否住宿', 'profiles[是否住宿]', $this->value('profiles/是否住宿'), '是')?>
		<input type="text" name="profiles[宿舍]" value="<?=$this->value('profiles/宿舍')?>" placeholder="宿舍">
		<input type="text" name="profiles[毕业初中]" value="<?=$this->value('profiles/毕业初中')?>" placeholder="毕业初中">
	</div>
	<div class="item">
		<div class="title"><label>联系方式</label></div>
		<input type="text" name="profiles[手机]" value="<?=$this->value('profiles/手机')?>" placeholder="手机">
		<input type="text" name="profiles[QQ]" value="<?=$this->value('profiles/QQ')?>" placeholder="QQ">
		<input type="text" name="profiles[电子邮件]" value="<?=$this->value('profiles/电子邮件')?>" placeholder="电子邮件">
		<input type="text" name="profiles[联系地址]" value="<?=$this->value('profiles/联系地址')?>" placeholder="联系地址">
		<input type="text" name="profiles[邮政编码]" value="<?=$this->value('profiles/邮政编码')?>" placeholder="邮政编码">
		<br>
		<input type="text" name="profiles[所属街道]" value="<?=$this->value('profiles/所属街道')?>" placeholder="所属街道">
		<input type="text" name="profiles[家庭电话]" value="<?=$this->value('profiles/家庭电话')?>" placeholder="家庭电话">
	</div>
	<div class="item">
		<div class="title"><label>其它信息</label></div>
		<input type="text" name="profiles[银行账号]" value="<?=$this->value('profiles/银行账号')?>" placeholder="银行账号">
		<textarea name="profiles[疾病史]" placeholder="疾病史"><?=$this->value('profiles/疾病史')?></textarea>
	</div>
<?php } ?>
</form>