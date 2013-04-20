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
	<div class="item">
		<div class="title"><label title="留空则不修改">设置用户名密码</label></div>
		<input type="text" name="user[username]" value="<?=$this->value('user/username')?>" placeholder="用户名" title="用户名" />
		<input type="password" name="user[password]" placeholder="密码" title="密码" />
		<input type="password" name="user[password_new]" placeholder="新密码" title="新密码" />
		<input type="password" name="user[password_new_confirm]" placeholder="新密码确认" title="新密码确认" />
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
</form>