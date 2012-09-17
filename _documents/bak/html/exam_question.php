<div class="inputTable">
<form name="form" method="post">
<div class="label"><? echo $part['name'] ?></div>
<p><? echo $part['intro']; ?></p>
<hr />

<div class="label"><? echo $question['id_in_exam'].'. '.$question['question'].' ('.$_SESSION['exam']['status']['currentQuestion'].'/'.$questions.')';
 ?></div>
<p>
<?php
if($question['type']=='choice'){
	$choices=unserialize($question['choices']);
	foreach($choices as $optionID => $option){
		echo "<label><input type='radio' name='q".$_SESSION['exam']['status']['currentQuestion']."' value='".$optionID."'>".$option.'</label>';
	}
}elseif($question['type']=='essay'){
	echo "<textarea class='item' name='q".$_SESSION['exam']['status']['currentQuestion']."' rows='10'></textarea>";
}
?>
</p>
<input class="submit" type="submit" name="questionSubmit" value="继续>>" />
</form>
</div>