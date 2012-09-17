<?php
if(got('var','minimized')){
	echo (bool)array_dir('_SESSION/minimized');
}
if(got('var','scroll')){
	echo array_dir('_SESSION/'.$_POST['controller'].'/'.$_POST['action'].'/scroll_top');
}
if(got('var','default_controller')){
	echo $_G['default_controller'];
}
?>