<?php
$_POST=array_trim($_POST);
if(isset($_POST)){
	$glue=$values='';
	foreach($_POST['document'] as $id=>$status){
		$values.=$glue."('".$id."','".$_SESSION['id']."','".time()."')";
		$glue=','."\n";
	}
	$q="REPLACE INTO document_fav (file,uid,time) values ".$values;
	db_query($q);
}
redirect('/document');
?>