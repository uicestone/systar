<? echo $date_range_bar?>
<select name="contribute_type" onchange="redirectPara(this)">
	<? displayOption(array('fixed'=>'固定贡献','actual'=>'实际贡献'),$this->input->get('contribute_type'),true)?>
</select>