<?php
if(got('id')){
	//获取指定的一个日程
	echo json_encode(schedule_fetch_single($_GET['id']));

}else{
	//获得当前视图的全部日历，根据$_GET['start'],$_GET['end'](timestamp)
	echo json_encode(schedule_fetch_range($_GET['start'],$_GET['end'],$_GET['staff'],$_GET['case']));
}
?>
