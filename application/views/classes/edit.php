<form method="post">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[classes]" value="保存" />
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title">名称</div>
			<div class="field"><?=$this->value('classes/name') ?></div>
		</div>

		<div class="item">
			<div class="title">班主任</div>
			<input type="text" name="classes_extra[class_teacher_name]" value="<?=$this->value('classes_extra/class_teacher_name') ?>" />
		</div>

		<div class="item">
			<div class="title">班委</div>
			<?=$leaders?>
		</div>

		<div class="submit">
			<input type="submit" name="submit[classes]" value="保存" />
		</div>
	</div>
</div>
</form>