<select name="contribute_type" onchange="redirectPara(this)">
	<?=options(array('fixed'=>'固定贡献','actual'=>'实际贡献'),$this->input->get('contribute_type'),NULL,true)?>
</select>
<form method="post">
	<button type="submit" name="distribute">发放</button>
</form>