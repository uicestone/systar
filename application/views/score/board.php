<div class="blackboard">
<form name="form" method="post" action="score.php">
	<h1>阅卷登分</h1>
    <div class="content">
        <div id="scoreForm">
            <label><? echo $_SESSION['score']['currentExam']['name'].' '.
			$_SESSION['score']['currentExam']['grade_name'].' '.
			$_SESSION['score']['currentExam']['course_name']; ?></label>&nbsp;
            <label><? echo $_SESSION['score']['currentExam']['part_name']; ?></label>&nbsp;
            <label>当前考生：<? echo $_SESSION['score']['currentStudent']['room'].' '.$_SESSION['score']['currentStudent']['seat'].'座'.'('.$_SESSION['score']['currentStudent_id_in_exam'].'/'.$_SESSION['score']['currentExam']['students'].')'; ?></label>
        	<div class="item">
	            <label style="font-size:24px"><? echo $_SESSION['score']['currentStudent']['name']; ?></label>&nbsp;
                <input name="score" type="text" id="score" value="<? echo array_dir('_SESSION/score/currentScore/score'); ?>" maxlength="20" size="20" onkeypress="keyPressHandler($('input[name=nextScore]'))" />
                <label><input name="is_absent" type="checkbox" value="1" id="is_absent" <? if(array_dir('_SESSION/score/currentScore/is_absent')) echo 'checked';?> onkeypress="keyPressHandler($('input[name=nextScore]'))">缺考</label>
            </div>
			<div class="submit">
				<input type="submit" name="backToPartChoose" value="保存并返回">
<? if($_SESSION['score']['currentStudent_id_in_exam']>1){?>
            	<input type="submit" name="previousScore" value="上一名">
<? }?>
<? if($_SESSION['score']['currentStudent_id_in_exam']<$_SESSION['score']['currentExam']['students']){?>
				<input type="submit" name="nextScore" id="nextScore" value="下一名">
<? }?>
            </div>
            
        </div>
        <div class="item" style="margin-top:20px;float:right;">
            <label>学号：<input type="text" name="studentNumForSearch" maxlength="6" style="width:100px;" /></label>
            <input type="submit" name="studentSearch" value="查找" />
        </div>
    </div>
</form>
</div>
<script type="text/javascript">
	$('input[name="score"]').select();
</script>