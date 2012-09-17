<?php
$action='addFileStatus';

if(is_posted('fileStatusSubmit')){//获取表单数据并校验
	unset($_POST['fileStatusSubmit']);
	$action='insertFileStatus';

	$_SESSION['file']['post']=$_POST;
	
	foreach($_POST as $k => $v){
		if(!in_array($k,Array())){//可以不填项
			if ($v==''){
				showMessage('表格未填写完整','warning');$action='addFileStatus';break;//不满足插入条件，改变为填表动作
			}
		}
	}

	if($action=='insertFileStatus'){
		$_SESSION['file']['post']['file']=$_GET['addStatus'];
		$_SESSION['file']['post']['time']=time();
		db_insert('file_status',$_SESSION['file']['post']);
		unset($_SESSION['file']['post']);
		
		//redirect('/file');
	}
}

if($action=='addFileStatus'){
	require 'view/file_addStatus.php';
}
?>