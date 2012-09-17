<?php
$_POST=array_trim($_POST);
unset($_POST['favDelete']);
print_r($_POST);
if(isset($_POST)){
	$condition = db_implode($_POST, $glue = ' OR ','file','=',"'","'", '`','key');
	$q="DELETE FROM document_fav WHERE (".$condition.") AND uid='".$_SESSION['id']."'";
	db_query($q);
}
redirect('/document');
?>