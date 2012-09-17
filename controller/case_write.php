<?php
if(got('case_fee_condition')){
	$id=intval($_POST['id']);
	$value=$_POST['value'];

	if($case_feeConditionPrepend=case_feeConditionPrepend($id,$value)){
		echo json_encode($case_feeConditionPrepend);
	}
}
?>