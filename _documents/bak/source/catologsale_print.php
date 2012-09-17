<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
mysql_query("
UPDATE client_catologsale SET status = 2 
WHERE id IN(
	SELECT id FROM(
		SELECT id FROM view_catolog_sale
	)id_printed
)
",$db_link);
redirect('/catologsale.php');
?>