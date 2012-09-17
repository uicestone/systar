<?php
if(got('name')){
	if(in_array($_GET['name'],array('head','foot','frame','nav'))){
		$path='view/common';
	}else{
		$path='view';
	}
	
	if(file_exists($path.'/'.$_GET['name'].'.php'))
		require $path.'/'.$_GET['name'].'.php';
	elseif(file_exists($path.'/'.$_GET['name'].'.htm'))
		require $path.'/'.$_GET['name'].'.htm';
}
?>