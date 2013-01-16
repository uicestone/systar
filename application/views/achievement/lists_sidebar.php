<select name="contribute_type" onchange="redirectPara(this)">
	<? displayOption(array('fixed'=>'固定贡献','actual'=>'实际贡献'),$this->input->get('contribute_type'),true)?>
</select>
<div>
<?=$this->table->generate($achievement_dashboard)?>
</div>
<div>
<?=$this->table->generate($achievement_sum)?>
</div>