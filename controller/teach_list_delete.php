<?php
$_SESSION['post']=array_trim($_POST);
if(isset($_SESSION['post']['teach'])){
	$condition = db_implode($_SESSION['post']['teach'], $glue = ' OR ','id','=',"'","'", '`','key');
	mysql_query("DELETE FROM teach where term='".$_SESSION['global']['current_term']."' AND (".$condition.')');
}
redirect($_SERVER['REQUEST_URI'],'js');
?>