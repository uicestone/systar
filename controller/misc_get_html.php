<?php
if(got('name')){
	if(in_array($_GET['name'],array('head','foot','frame','nav'))){
		$path='view/common';
	}else{
		$path='view';
	}
	
	if(is_file($path.'/'.$_GET['name'].'.php'))
		require $path.'/'.$_GET['name'].'.php';
	elseif(is_file($path.'/'.$_GET['name'].'.htm'))
		require $path.'/'.$_GET['name'].'.htm';
}
?>