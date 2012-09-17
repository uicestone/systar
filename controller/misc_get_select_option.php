<?php
$select_type=intval($_POST['select_type']);

if($select_type){
	if(function_exists($_POST['affair'])){
		$options=call_user_func($_POST['affair'],$_POST['active_value']);
	}
	
	displayOption($options,NULL,true);

}else{
	$q_get_options="SELECT type FROM type WHERE affair='".$_POST['affair']."' AND classification='".$_POST['active_value']."'";
	$options_array=db_toArray($q_get_options);
	$options=array_sub($options_array,'type');
	
	displayOption($options);
}
?>