<?php
//教师列表界面
$student_num = $_SESSION['id'];
$q_teacher="SELECT * FROM `view_teach` WHERE `class`='".$_SESSION['class']."' AND `term`='".$_SESSION['global']['current_term']."'";
$r_teacher=mysql_query($q_teacher);

?>
<div class="blackboard">
<div class="blackboard_bottom"><!--弹簧黑板框，可自动伸展一倍-->
    <div class="flag">
    	<span><?php echo $_SESSION['class_name'];?></span>
    </div>
<div style="float:right;">
<table width="570" style="margin-right:30px;" border="0" align="center" cellspacing="1">
  <thead class="fontstyle1">
    <td><div align="center">教师</div></td>
    <td><div align="center">学科</div></td>
    <td><div align="center">评教</div></td>
  </thead>
<?php
$i=0;
$_SESSION['stu_teachers_Uggew7ac']=array();
while($teacher=mysql_fetch_array($r_teacher)){
	$odd = pow(-1,$i);
	$_SESSION['stu_teachers_Uggew7ac'][$i]=$teacher['teacher'];
?>
  <tr class="fontstyle2" <? if($odd==1)echo "bgcolor=#002200";?>>
    <td width="25%"><div align="center"><?php echo $teacher['teacher_name']; ?></div></td>
    <td width="25%"><div align="center"><?php echo $teacher['course_name']; ?></div></td>
    <td width="25%"><div align="center"><a href="javascript:show('/pingjiao.php?action=score&teacher=<?php echo $teacher['teacher']; ?>&teacher_name=<?php echo $teacher['teacher_name']; ?>',1,550,550,50,50)">进入</a></div></td></tr>
<?php 
	$i+=1;
}
?>
</table>
</div>
</div>
</div>