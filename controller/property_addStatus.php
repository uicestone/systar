<?php
$action='addPropertyStatus';

if(is_posted('propertyStatusSubmit')){//获取表单数据并校验
	unset($_POST['propertyStatusSubmit']);
	$action='insertPropertyStatus';

	$_SESSION['property']['post']=$_POST;
	
	foreach($_POST as $k => $v){
		if(!in_array($k,Array())){//可以不填项
			if ($v==''){
				showMessage('表格未填写完整','warning');$action='addPropertyStatus';break;//不满足插入条件，改变为填表动作
			}
		}
	}

	if($action=='insertPropertyStatus'){
		$_SESSION['property']['post']['property']=$_GET['addStatus'];
		$_SESSION['property']['post']['time']=time();
		db_insert('property_status',$_SESSION['property']['post']);
		unset($_SESSION['property']['post']);
		
		redirect('/property');
	}
}

if($action=='addPropertyStatus'){
	require 'view/property_addStatus.php';
}
?>