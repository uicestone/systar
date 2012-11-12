<select name="contribute_type" onchange="redirectPara(this)">
	<? displayOption(array('fixed'=>'固定贡献','actual'=>'实际贡献'),$this->input->get('contribute_type'),true)?>
</select>
<form method="post">
	<input type="submit" name="distribute" value="发放" />
</form>