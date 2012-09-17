<?php
$staff=intval($_GET['staff']);
$indicator=intval($_POST['indicator']);
//$anonymous=intval($_POST['anonymous']);

$field=$value=NULL;

if(is_posted('field') && is_posted('value')){
	$field=$_POST['field'];
	$value=$_POST['value'];
}

if($evaluation_insert_score=evaluation_insert_score($indicator,$staff,$field,$value/*,$anonymous*/)){
	echo json_encode($evaluation_insert_score);
}
?>