<?php
if(!is_posted('id')){//插入新的任务
	echo schedule_add($_POST);
	unset($_SESSION['schedule']['post']);
	
}elseif(is_posted('action','delete')){//删除任务
	schedule_delete($_POST['id']);

}elseif(is_posted('action','updateContent')){//更新任务内容
	schedule_update($_POST['id'],array(
		'content'=>$_POST['content'],
		'experience'=>$_POST['experience'],
		'completed'=>$_POST['completed'],
		'fee'=>(float)$_POST['fee'],
		'fee_name'=>$_POST['fee_name'],
		'place'=>$_POST['place']
	));

}else{//更新任务时间
	$timeDelta=intval($_POST['dayDelta'])*86400+intval($_POST['minuteDelta'])*60;
	
	if(is_posted('action','resize')){
		$data['hours_own']="_`hours_own`+'".($timeDelta/3600)."'_";
	}elseif(is_posted('action','drag')){
		$data['time_start']="_`time_start`+'".$timeDelta."'_";
	}
	
	$data['all_day']=(int)$_POST['allDay'];
	$data['time_end']="_time_end+'".$timeDelta."'_";
	
	schedule_update($_POST['id'],$data);
}
?>