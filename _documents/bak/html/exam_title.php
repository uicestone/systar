<div class="inputTable">
<form name="form" method="post">
<div class="label"><? echo $exam['ui_name'] ?></div>
<p><? echo $exam['intro']; ?></p>
<hr />
<?php
echo sessioned('mod','title',false)?"<input class='submit' type='submit' name='enterExam' value='进入测试>>' />"
	:
	"<div class='label'>本次测试已经完成，谢谢你的参与</div>";
?>
</form>
</div>
