<?php
$q="
SELECT staff.name AS staff_name,ROUND((account_sum.sum-600000)*0.04*staff.modulus,2) AS bonus_sum
FROM staff CROSS JOIN 
(
	SELECT SUM(amount) AS sum 
	FROM account
	WHERE name IN('律师费','顾问费','咨询费')
";

$date_range_bar=dateRange($q,'time_occur');

$q.="
)account_sum
WHERE (account_sum.sum-600000)*0.04*staff.modulus>0
";
processOrderby($q,'staff.id','ASC',array('staff_name'));

$q_rows="SELECT COUNT(id) FROM staff WHERE modulus>0";

$listLocator=processMultiPage($q,$q_rows);

$field=array(
	'staff_name'=>array('title'=>'人员'),
	'bonus_sum'=>array('title'=>'团奖')
);

$menu=array(
'head'=>'<div class="right">'.
			$listLocator.
		'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field);
?>