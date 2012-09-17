<?php
$q="
	SELECT 
		view_teach.teacher AS id,view_teach.teacher_name AS teacher_name,count(result.student) as vote_num, sum(if(result.suggest='',0,1)) AS suggests,
		round(avg(`1`),1) as `1`,	round(avg(`2`),1) as `2`,
		round(avg(`3`),1) as `3`,	round(avg(`4`),1) as `4`,
		round(avg(`5`),1) as `5`,	round(avg(`6`),1) as `6`,
		round(avg(`7`),1) as `7`,	round(avg(`8`),1) as `8`,
		round(avg(`9`),1) as `9`,	round(avg(`10`),1) as `10`,
		round(avg(`11`),1) as `11`,	round(avg(`12`),1) as `12`,
		round(avg(`13`),1) as `13`,	round(avg(`14`),1) as `14`,
		round(avg(`15`),1) as `15`,	round(avg(`16`),1) as `16`,
		round(avg(`17`),1) as `17`,
		round(avg(sum),2) as avg
	FROM view_teach INNER JOIN result ON (view_teach.teacher=result.teacher AND view_teach.student=result.student AND result.term='".$_SESSION['global']['current_term']."' )
";

$rangeMenu=processRange($q,array('class'=>'view_teach.class','grade'=>'view_teach.grade'));

$q.=" GROUP BY result.teacher";

processOrderby($q,'sum','DESC',array('teacher_name'));

$listLocator=processMultiPage($q);

$fields=array(
	'id'=>'编号',
	'teacher_name'=>array('title'=>'姓名','td_title'=>'style="width:4em;"'),
	'vote_num'=>'人数',
'1'=>'敬业精神','2'=>'师德修养','3'=>'教学组织','4'=>'语言表达','5'=>'现代技术','6'=>'板书质量','7'=>'上课准备','8'=>'教学广度','9'=>' 重点透彻','10'=>'方法多样','11'=>'教学控制','12'=>'准时到岗','13'=>'作业适量','14'=>'及时批改','15'=>'课后辅导','16'=>'学生热情','17'=>'学生水平','avg'=>'平均',
	'suggests'=>array('title'=>'学生意见','content'=>'{suggests}<a href="/pingjiao.php?mod=admin&action=suggest&teacher={id}" target="_blank">查看</a>','td_title'=>'width="70px"')
);

$submitBar=array(
	'head'=>'<div style="float:right;">'.
				$rangeMenu.
				$listLocator.
			'</div>'

);

exportTable($q,$fields,$submitBar);
?>