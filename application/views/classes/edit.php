<form method="post">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[classes]" value="保存" />
		<? if($this->as_popup_window){?>
		<input type="submit" name="submit[cancel]" value="关闭" />
		<? }?>
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title">名称</div>
			<div class="field"><?=post('classes/name') ?></div>
		</div>

		<div class="item">
			<div class="title">班主任</div>
			<input type="text" name="classes_extra[class_teacher_name]" value="<?=post('classes_extra/class_teacher_name') ?>" />
		</div>

		<div class="item">
			<div class="title">班委</div>
			<?=$leaders?>
		</div>

		<div class="submit">
			<input type="submit" name="submit[classes]" value="保存" />
			<input type="submit" name="submit[cancel]" value="关闭" />
		</div>
	</div>
</div>
</form>