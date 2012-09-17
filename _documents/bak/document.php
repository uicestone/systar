<?php
define('IN_UICE','document');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(is_posted('fileSubmit') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_upload';
	$_G['require_export']=false;

}elseif(is_posted('createDirSubmit') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_createDir';
	$_G['require_export']=false;

}elseif(is_posted('fav') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_fav';
	$_G['require_export']=false;

}elseif(is_posted('favDelete') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_fav_delete';
	$_G['require_export']=false;

}elseif(got('view') && is_permitted(IN_UICE)){//根据目录ID进行定位/文件ID则进行下载
	
	$_SESSION[IN_UICE]['option']['in_search_mod']=false;

	$_G['action']=IN_UICE.'_list';
	
	$q_folder="SELECT * FROM `document` WHERE id=".$_GET['view'];
	$r_folder=mysql_query($q_folder,$db_link);
	$folder=mysql_fetch_array($r_folder);

	if($folder['type']!=''){
		$_G['action']="document_download";
		$_G['require_export']=false;
	}else{
		$_SESSION[IN_UICE]['upID']=$folder['parent'];
		$_SESSION[IN_UICE]['currentDir']=$folder['name'];
		$_SESSION[IN_UICE]['currentDirID']=$folder['id'];
		$_SESSION[IN_UICE]['currentPath']=$folder['path'];
		$_SESSION[IN_UICE]['currentLevel']=$folder['level']+1;//当前列表中文件的级别，是当前目录本身级别+1
	}

}elseif(got('file') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_file';

}elseif(is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_list';
}

require 'controller/export.php';
?>