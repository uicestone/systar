<?php
$_SESSION['teach']['post']=array_trim($_POST);
if(isset($_SESSION['teach']['post']['teachOfOneClass'])){
	$teachersOfThisClass=explode(' ',$_SESSION['teach']['post']['teachOfOneClass']);

	$glue='';$values='';
	foreach($teachersOfThisClass as $newTeacher){
		$values.=$glue."('".$newTeacher."','".$_SESSION['teach']['option']['currentClass']."','".$_SESSION['global']['current_term']."')";
		$glue=','."\n";
	}
	mysql_query("INSERT INTO teach (teacher, class,term) values ".$values);
}

redirect('/teach.php');
?>