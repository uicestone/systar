<?php
$q="
	SELECT each_other.staff,staff.name AS staff_name,each_other.score AS each_other,each_other.critics,self.score AS self,manager.score AS manager
	FROM
	(
		SELECT staff,AVG(sum_score) AS score,COUNT(sum_score) AS critics
		FROM (
		SELECT staff,SUM(score) AS sum_score
		FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
		WHERE uid <> '6356' AND staff<>uid
		GROUP BY uid,staff
		)sum
		GROUP BY staff
	)each_other
	LEFT JOIN(
		SELECT staff,SUM(score) AS score
		FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
		WHERE uid = '6356'
		GROUP BY uid,staff
	)manager USING (staff) 
	LEFT JOIN(
		SELECT staff,SUM(score) AS score
		FROM `evaluation_score` INNER JOIN evaluation_indicator ON evaluation_score.indicator=evaluation_indicator.id
		WHERE uid = staff
		GROUP BY uid,staff
	)self USING(staff)
	INNER JOIN staff ON staff.id=each_other.staff	
";

processOrderby($q,'staff');

$field=array(
	'staff_name'=>array('title'=>'姓名'),
	'each_other'=>array('title'=>'互评','content'=>'{each_other}({critics})'),
	'self'=>array('title'=>'自评'),
	'manager'=>array('title'=>'主管评分')
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field);
?>