<?php
$_SESSION['post']=array_trim($_POST);

if(isset($_SESSION['post']['unchanged'])){
	$condition = db_implode($_SESSION['post']['unchanged'], $glue = ' OR ','id','=',"'","'", '`','key');
	
	mysql_query("INSERT INTO teach (teacher_num,class_num,term)
	(SELECT teacher,class,'".$_SESSION['global']['current_term']."' as term FROM `teach` where ".$condition.')');
}

if(isset($_SESSION['post']['changeTo'])){
	$glue='';$values='';
	foreach($_SESSION['post']['changeTo'] as $class => $array){
		foreach($array as $teach => $newTeacher){
			$values.=$glue."('".$newTeacher."','".$class."','".$_SESSION['global']['current_term']."')";
			$glue=','."\n";
		}
	}
	
	mysql_query("INSERT INTO teach (teacher,class,term) values ".$values);
}

redirect($_SERVER['REQUEST_URI'],'js');
?>