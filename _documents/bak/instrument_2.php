<?php
$paper=$item=array();$line='';

$line='委托人：'.$condition['client'][$condition['current_client']]['name'];

$item[]=$line;

$line='身份证号码：'.$condition['client'][$condition['current_client']]['id_card'];

$item[]=$line;

$line='地址：'.$condition['client'][$condition['current_client']]['address'];

$item[]=$line;

$line='联系电话：'.$condition['client'][$condition['current_client']]['phone'];

$item[]=$line;

$paper[]=$item;
$item=array();
//添加一级段落

$line='受委托人：'.$condition['client'][$condition['current_client']]['name'];

$item[]=$line;

$line='身份证号码：'.$condition['client'][$condition['current_client']]['id_card'];

$item[]=$line;

$line='地址：'.$condition['client'][$condition['current_client']]['address'];

$item[]=$line;

$line='联系电话：'.$condition['client'][$condition['current_client']]['phone'];

$item[]=$line;

$paper[]=$item;
$item=array();
//添加一级段落

foreach($paper as $id => $item){
	foreach($item as $lineid => $line){
		if($lineid==0 && $id!=0){
			echo '<p>'.num_to_chn($id).'、'.$line.'</p>';
		}else{
			echo '<p>　　'.$line.'</p>';
		}
	}
}
?>