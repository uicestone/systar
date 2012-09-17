<?php
$type=NULL;
got('type') && $type=$_GET['type'];

$result=client_match($_POST['term'],'client',$type);

$array=array();

foreach($result as $line_id => $content_array){
	$array[$line_id]['label']=$content_array['name'];
	$array[$line_id]['value']=$content_array['id'];
}
echo json_encode($array);
?>