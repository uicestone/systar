<?=javascript('student_add')?>
<form method="post" name="<?=CONTROLLER?>" id="<?=$this->student->id?>">
<div class="contentTableMenu">
	<div class="right">
		<button type="submit" name="submit[student]">保存</button>
	</div>
</div>
<div class="contentTableBox">
	<div class="item">
		<div class="title"><label>基本：</label></div>
		<div>
			<input type="text" name="student[id_card]" value="<?=$this->value('student/id_card'); ?>" placeholder="身份证" class="right" style="width:39%" />
			<input type="text" name="student[name]" value="<?=$this->value('student/name'); ?>" placeholder="姓名" style="width:59%" />
		</div>
		<input type="text" name="student[birthday]" value="<?=$this->value('student/birthday'); ?>" class="birthday right" placeholder="生日" style="width:39%" />
		<input name="student[race]" value="<?=$this->value('student/race'); ?>" type="text" placeholder="民族" class="right" style="width:30%;margin-right:1%" />
		<?=radio(array('男','女'),'student[gender]',$this->value('student/gender'))?>
		&nbsp;&nbsp;
		<?=checkbox('团员','student[youth_league]',$this->value('student/youth_league'),'1')?>
	</div>

	<div class="item">
		<div class="title"><label>生源：</label></div>
		<label>类别：</label><input name="student[type]" value="<?=$this->value('student/type'); ?>" type="text" style="width:40%" disabled />
		<label>初中：</label><input name="student[junior_school]" value="<?=$this->value('student/junior_school'); ?>" type="text" style="width:40%" />
	</div>

	<div class="item">
		<div class="title"><label>班级-学号：</label></div>
		<select name="student_class[class]"<? if(!$this->user->isLogged('jiaowu'))echo' disabled'?> style="width:20%">
		<?=options($this->classes->getRelatedTeams(NULL,NULL,'class'),$this->value('student_class/class'),true)?>
		</select>
		<input type="text" name="student_class[num_in_class]" title="班中学号" value="<?=$this->value('student_class/num_in_class')?>" placeholder="班中学号"<? if(!$this->user->isLogged('jiaowu'))echo' disabled'?> style="width:20%" />
		<span class="field">班主任：<?=$this->value('student_extra/class_teacher_name') ?></span>
	</div>

	<div class="item">
		<div class="title"><label>联系方式：</label></div>
		<div>
		<?=checkbox('住宿','student[resident]',$this->value('student/resident'),'1')?>
		<input type="text" name="student[dormitory]" value="<?=$this->value('student/dormitory'); ?>"<? if(!$this->value('student/resident'))echo ' disabled'?> placeholder="宿舍" style="width:20%" />
		<input type="text" name="student[mobile]" value="<?=$this->value('student/mobile'); ?>" placeholder="手机" style="width:20%" />
		<input type="text" name="student[phone]" value="<?=$this->value('student/phone'); ?>" placeholder="固定电话" style="width:20%" />
		<input type="text" name="student[email]" value="<?=$this->value('student/email'); ?>" placeholder="电子邮件" style="width:29%" />
		</div>
		<div>
		<input type="text" name="student[neighborhood_committees]" value="<?=$this->value('student/neighborhood_committees'); ?>" placeholder="居委会" class="right" style="width:15%" />
		<input type="text" name="student[address]" value="<?=$this->value('student/address'); ?>" placeholder="地址" style="width:83%" />
		</div>
	</div>

	<div class="item">
		<div class="title"><label>亲属</label></div>

		<?=$relatives?>

		<div id="studentRelativesAddForm">
			<input type="text" name="student_relatives[name]" value="<?=$this->value('student_relatives/name')?>" placeholder="姓名" style="width:20%" />

			<select name="student_relatives[relationship]" style="width:20%">
				<?=options(array('父','母','其他'),$this->value('student_relatives/relationship'))?>
			</select>
			<input type="text" name="student_relatives[contact]" value="<?=$this->value('student_relatives/contact')?>" placeholder="联系电话" style="width:25%" />
			<input type="text" name="student_relatives[work_for]" value="<?=$this->value('student_relatives/work_for')?>" placeholder="工作单位" style="width:25%" />
			<button type="submit" name="submit[student_relatives]">添加</button>
		</div>
	 </div>

	<div class="item">
		<div class="title">银行账号：</div>
		<input type="text" name="student[bank_account]" value="<?=$this->value('student/bank_account'); ?>" />
	</div>

	<div class="item">
		<div class="title">疾病史：</div>
		<textarea name="student[disease_history]" rows="2"><?=$this->value('student/disease_history'); ?></textarea>
	</div>

	<div class="item">
		<div class="title"><label>成绩</label>
		<a href="/student/viewscore?student=<? echo $this->value('student/id') ?>" style="font-size:12px">查看详细</a></div>
		<?=$scores?>
	</div>

	<div class="item">
		<div class="title"><label>奖惩记录</label></div>

		<?=$behaviour?>

		<? if($this->user->isLogged('jiaowu')){ ?>
		<div id="studentBehaviourAddForm">
			<select name="student_behaviour[type]" style="width:10%">
			<?=options(array('_ENUM','student_behaviour','type'),$this->value('student_behaviour/type')) ?>
			</select>

			<input type="text" name="student_behaviour[date]" value="<?=$this->value('student_behaviour/date')?>" placeholder="日期" class="date" style="width:20%" />

			<input type="text" name="student_behaviour[name]" value="<?=$this->value('student_behaviour/name')?>" placeholder="概要" style="width:40%" />

			<select name="student_behaviour[level]" style="width:20%">
			<?=options(array('_ENUM','student_behaviour','level'),$this->value('student_behaviour/level')) ?>
			</select>

			<button type="submit" name="submit[student_behaviour]">添加</button>
			<br />
			<textarea name="student_behaviour[content]" placeholder="具体事项记载" rows="1"><?=$this->value('student_behaviour/content') ?></textarea>
		</div>
		<? } ?>
	 </div>

	<? if($this->user->isLogged('teacher') || $this->user->isLogged('parent')){ ?>
	<div class="item">
		<div class="title"><label>家校互动</label>
			<a href="student?interactive" style="font-size:12px">查看详细</a>
		</div>

		<?=$comments?>

		<div id="studentCommentAddForm">
			<input type="text" name="student_comment[title]" value="<?=$this->value('student_comment/title') ?>" placeholder="标题" style="width:80%" />
			<!--<input type="text" name="student_comment_extra[recipients_name]" value="<?=$this->value('student_comment_extra/recipients_name') ?>" placeholder="密送至" title="留空则家长和所有任课老师可见" style="width:10%" />-->
			<button type="submit" name="submit[student_comment]">保存</button>
			<br />
			<textarea name="student_comment[content]" placeholder="正文"><?=$this->value('student_comment/content') ?></textarea>
		</div>
	 </div>
	 <? } ?>

	<div class="submit">
		<button type="submit" name="submit[student]">保存</button>			
	</div>
</div>
</form>