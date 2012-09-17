<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
$q="SELECT * FROM cron WHERE name = 'courtUpdate'";
$r=mysql_query($q,$db_link);
$lastrun=$r['lastrun'];
$q="UPDATE court SET status = 2 WHERE id IN (SELECT DISTINCT case FROM client_catologsale AND time > '".$lastrun."') AND time > '".$lastrun."'";
mysql_query($q,$db_link);

$q="UPDATE court SET status = 1 WHERE status=0";
mysql_query($q,$db_link);

redirect($_SERVER['REQUEST_URI'],'js');
?>