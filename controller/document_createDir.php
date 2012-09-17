<?php
$dirPath=iconv("utf-8","gbk",$_SESSION['document']['currentPath']."/".$_POST['dirName']);
mkdir($dirPath);
$dir=array(
	'name'=>$_POST['dirName'],
	'parent'=>$_SESSION['document']['currentDir'],
	'level'=>$_SESSION['document']['currentLevel'],
	'path'=>$_SESSION['document']['currentPath']."/".$_POST['dirName'],
	'parent'=>$_SESSION['document']['currentDirID'],
	'type'=>''
);
db_insert('document',$dir);

redirect('/document');
?>