<?php
$action='addProperty';

if(is_posted('propertySubmit')){//获取表单数据并校验
	unset($_POST['propertySubmit']);
	$action='insertProperty';

	$_SESSION['property']['post']=$_POST;
	
	foreach($_POST as $k => $v){
		if(!in_array($k,Array('comment'))){//可以不填项
			if ($v==''){
				showMessage('表格未填写完整','warning');$action='addProperty';break;//不满足插入条件，改变为填表动作
			}
		}
	}
	if($action=='insertProperty'){
		$property=db_insert('property',$_SESSION['property']['post']);
		unset($_SESSION['property']['post']);
		
		$_SESSION['property']['post']['property']=$property;
		$_SESSION['property']['post']['time']=time();
		$_SESSION['property']['post']['status']='新添加';
		$_SESSION['property']['post']['is_out']=0;
		db_insert('property_status',$_SESSION['property']['post']);
		unset($_SESSION['property']['post']);

		$action='addProperty';
		showMessage('添加成功，可以继续添加','notice');
	}
}
if($action=='addProperty'){
	require 'view/property_add.php';
}
?>