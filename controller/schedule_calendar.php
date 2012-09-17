<?php
model('achievement');
model('company');

$q_news="SELECT * FROM `news` WHERE display=1 AND company='".$_G['company']."' ORDER BY time DESC LIMIT 5";
$field_news=array(
	'title'=>array(
		'title'=>'公告 <a href="news" style="font-size:14px">更多</a>',
		'surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'news?edit={id}\')'),
		'eval'=>true,
		'content'=>"
			\$return='{title}';
			if('{time}'>\$_G['timestamp']-86400*7){
				\$return.=' <img src=\"images/new.gif\" alt=\"new\" />';
			}
			return \$return;
		",
		'orderby'=>false
	),
);

$sidebar_table=array();
if(function_exists($_G['syscode'].'_'.'schedule_side_table')){
	$sidebar_table=call_user_func($_G['syscode'].'_'.'schedule_side_table');
}
?>