<?php
if ($_FILES["file"]["error"] > 0){
	echo "error code: " . $_FILES["file"]["error"] . "<br />";
}
else{
	$storePath=iconv("utf-8","gbk",$_SESSION['document']['currentPath']."/".$_FILES["file"]["name"]);//存储路径转码
	
	if (file_exists($storePath)){
		unlink(file_exists($storePath));
		$db_replace=true;
	}else{
		$db_replace=false;
	}
	
	move_uploaded_file($_FILES["file"]["tmp_name"], $storePath);

	if(preg_match('/\.(\w*?)$/',$_FILES["file"]["name"], $extname_match)){
		$_FILES["file"]["type"]=$extname_match[1];
	}else
		$_FILES["file"]["type"]='none';
	$fileInfo=array(
		'name'=>$_FILES["file"]["name"],
		'type'=>$_FILES["file"]["type"],
		'size'=>$_FILES["file"]['size'],
		'parent'=>$_SESSION['document']['currentDirID'],
		'path'=>$_SESSION['document']['currentPath']."/".$_FILES["file"]["name"],
		'comment'=>$_POST['comment'],
		'uid'=>$_SESSION['id'],
		'username'=>$_SESSION['username'],
		'time'=>$_G['timestamp']
	);
	db_insert('document',$fileInfo,false,$db_replace);
	redirect('/document');
}
?>