<?php
function uidTime($company=true,$time_insert=false){
	$CI=&get_instance();
	$array=array(
		'uid'=>$CI->user->id,
		'time'=>$CI->date->now,
	);
	
	if($company){
		$array['company']=$CI->company->id;
	}
	
	if($time_insert){
		$array['time_insert']=$CI->date->now;
	}
	
	return $array;
}	
?>
