<p><?=$schedule['time_start']?>(<?=$schedule['hours_own']?>小时)</p>
<span>案件：<?=$schedule['case_name']?></span>
<?if($schedule['case']<20 && $schedule['case']>10){?>
<span>，客户：<?=$schedule['client_name']?></span>
<?}?>
<hr />
<? foreach ($schedule['content_paras'] as $para){?>
	<p><?=$para?></p>
<?}?>
<hr />
<? foreach ($schedule['experience_paras'] as $para){?>
	<p><?=$para?></p>
<?}?>