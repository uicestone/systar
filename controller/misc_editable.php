<?php
if(count($_POST)>2){
	exit('post data error');
}

$value=$table=$field='';
$id=0;

foreach($_POST as $k => $v){
	if(preg_match('/-id$/',$k)){
		//id项
		$table=substr($k,0,-3);
		$id=$v;
	}else{
		$field=$k;
		$value=$v;
	}
}

$data=array($field=>$value);

db_update($table,$data,"id='".$id."'");

echo db_fetch_field("SELECT `".$field."` FROM `".$table."` WHERE id='".$id."'");
?>