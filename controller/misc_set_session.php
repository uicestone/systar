<?php
if(is_posted('minimized')){
	array_dir('_SESSION/minimized',(bool)$_POST['minimized']);
	echo 'success';
}

if(got('scroll')){
	array_dir('_SESSION/'.$_POST['controller'].'/'.$_POST['action'].'/scroll_top',$_POST['scrollTop']);
}
?>