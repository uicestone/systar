<?php
model('staff');
$staff=staff_fetch(client_check($_POST['client_name'],'source_lawyer'));
if($staff){
	echo $staff['name'];
}
?>