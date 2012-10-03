<? javascript('student_add')?>
<form method="post">
<div class="contentTableMenu">
	<div class="right">
		<input type="submit" name="submit[student]" value="保存" />
		<? if(!$_G['as_controller_default_page']){ ?>
		<input type="submit" name="submit[cancel]" value="关闭" />
		<? } ?>
	</div>
</div>
<div class="contentTableBox">
	<div class="contentTable">
		<div class="item">
			<div class="title"><label>基本：</label></div>
			<div>
			<input type="text" name="student[id_card]" value="<? displayPost('student/id_card'); ?>" placeholder="身份证" class="right" style="width:39%" />
			<input type="text" name="student[name]" value="<? displayPost('student/name'); ?>" placeholder="姓名" style="width:59%" />
			</div>
			<input type="text" name="student[birthday]" value="<? displayPost('student/birthday'); ?>" class="birthday right" placeholder="生日" style="width:39%" />
			<input name="student[race]" value="<? displayPost('student/race'); ?>" type="text" placeholder="民族" class="right" style="width:30%;margin-right:1%" />
			<? displayRadio(array('男','女'),'student[gender]',post('student/gender'))?>
			&nbsp;&nbsp;
			<? displayCheckbox('团员','student[youth_league]',post('student/youth_league'),'1')?>
		</div>

		<div class="item">
			<div class="title"><label>生源：</label></div>
			<label>类别：</label><input name="student[type]" value="<? displayPost('student/type'); ?>" type="text" style="width:40%" disabled="disabled" />
			<label>初中：</label><input name="student[junior_school]" value="<? displayPost('student/junior_school'); ?>" type="text" style="width:40%" />
		</div>

		<div class="item">
			<div class="title"><label>班级-学号：</label></div>
			<select name="student_class[class]"<? if(!is_logged('jiaowu'))echo' disabled="disabled"'?> style="width:20%">
			<? displayOption(NULL,post('student_class/class'),true,'class','grade','name',"grade>='".$_SESSION['global']['highest_grade']."'")?>
			</select>
			<input type="text" name="student_class[num_in_class]" title="班中学号" value="<? displayPost('student_class/num_in_class')?>" placeholder="班中学号"<? if(!is_logged('jiaowu'))echo' disabled="disabled"'?> style="width:20%" />
			<span class="field">班主任：<? displayPost('student_extra/class_teacher_name') ?></span>
		</div>

		<div class="item">
			<div class="title"><label>联系方式：</label></div>
			<div>
			<? displayCheckbox('住宿','student[resident]',post('student/resident'),'1')?>
			<input type="text" name="student[dormitory]" value="<? displayPost('student/dormitory'); ?>"<? if(!post('student/resident'))echo ' disabled="disabled"'?> placeholder="宿舍" style="width:20%" />
			<input type="text" name="student[mobile]" value="<? displayPost('student/mobile'); ?>" placeholder="手机" style="width:20%" />
			<input type="text" name="student[phone]" value="<? displayPost('student/phone'); ?>" placeholder="固定电话" style="width:20%" />
			<input type="text" name="student[email]" value="<? displayPost('student/email'); ?>" placeholder="电子邮件" style="width:29%" />
			</div>
			<div>
			<input type="text" name="student[neighborhood_committees]" value="<? displayPost('student/neighborhood_committees'); ?>" placeholder="居委会" class="right" style="width:15%" />
			<input type="text" name="student[address]" value="<? displayPost('student/address'); ?>" placeholder="地址" style="width:83%" />
			</div>
		</div>

		<div class="item">
			<div class="title"><label>亲属</label></div>

			<? exportTable($q_student_relatives,$field_student_relatives,NULL,false,false);?>

			<div id="studentRelativesAddForm">
				<input type="text" name="student_relatives[name]" value="<? displayPost('student_relatives/name')?>" placeholder="姓名" style="width:20%" />

				<select name="student_relatives[relationship]" style="width:20%">
					<? displayOption(array('父','母','其他'),post('student_relatives/relationship'))?>
				</select>
				<input type="text" name="student_relatives[contact]" value="<? displayPost('student_relatives/contact')?>" placeholder="联系电话" style="width:25%" />
				<input type="text" name="student_relatives[work_for]" value="<? displayPost('student_relatives/work_for')?>" placeholder="工作单位" style="width:25%" />
				<input type="submit" name="submit[student_relatives]" value="添加" />
			</div>
		 </div>

		<div class="item">
			<div class="title">银行账号：</div>
			<input type="text" name="student[bank_account]" value="<? displayPost('student/bank_account'); ?>" />
		</div>

		<div class="item">
			<div class="title">疾病史：</div>
			<textarea name="student[disease_history]" rows="2"><? displayPost('student/disease_history'); ?></textarea>
		</div>
		
		<div class="item">
			<div class="title"><label>成绩</label>
			<a href="student?viewscore&student=<? echo post('student/id') ?>" style="font-size:12px">查看详细</a></div>
			<? arrayExportTable($scores,NULL,false,false,array(),false,true) ?>
		</div>

		<div class="item">
			<div class="title"><label>奖惩记录</label></div>

			<? exportTable($q_student_behaviour,$field_student_behaviour,NULL,false,false);?>

			<? if(is_logged('jiaowu')){ ?>
			<div id="studentBehaviourAddForm">
				<select name="student_behaviour[type]" style="width:10%">
				<? displayOption(array('_ENUM','student_behaviour','type'),post('student_behaviour/type')) ?>
				</select>

				<input type="text" name="student_behaviour[date]" value="<? displayPost('student_behaviour/date')?>" placeholder="日期" class="date" style="width:20%" />

				<input type="text" name="student_behaviour[name]" value="<? displayPost('student_behaviour/name')?>" placeholder="概要" style="width:40%" />
				
				<select name="student_behaviour[level]" style="width:20%">
				<? displayOption(array('_ENUM','student_behaviour','level'),post('student_behaviour/level')) ?>
				</select>

				<input type="submit" name="submit[student_behaviour]" value="添加" />
				<br />
				<textarea name="student_behaviour[content]" placeholder="具体事项记载" rows="1"><? displayPost('student_behaviour/content') ?></textarea>
			</div>
			<? } ?>
		 </div>

		<? if(is_logged('teacher') || is_logged('parent')){ ?>
		<div class="item">
			<div class="title"><label>家校互动</label>
				<a href="student?interactive" style="font-size:12px">查看详细</a>
			</div>

			<? exportTable($q_student_comment,$field_student_comment,NULL,false,false);?>

			<div id="studentCommentAddForm">
				<input type="text" name="student_comment[title]" value="<? displayPost('student_comment/title') ?>" placeholder="标题" style="width:80%" />
				<!--<input type="text" name="student_comment_extra[recipients_name]" value="<? displayPost('student_comment_extra/recipients_name') ?>" placeholder="密送至" title="留空则家长和所有任课老师可见" style="width:10%" />-->
				<input type="submit" name="submit[student_comment]" value="保存" />
				<br />
				<textarea name="student_comment[content]" placeholder="正文"><? displayPost('student_comment/content') ?></textarea>
			</div>
		 </div>
		 <? } ?>

		<div class="submit">
			<input type="submit" name="submit[student]" value="保存" />
			<? if(!$_G['as_controller_default_page']){ ?>
			<input type="submit" name="submit[cancel]" value="关闭" />
			<? } ?>
		</div>
	</div>
</div>
</form>