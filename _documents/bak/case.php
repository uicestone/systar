<?php
define('IN_UICE','case');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(is_posted('submit/cancel') && is_permitted(IN_UICE)){
	$_G['action']='misc_cancel';
	$_G['require_export']=false;
	
}elseif(got('document')){
	if(got('list')){
		$_G['action']=IN_UICE.'_document_list';
	}else{
		$_G['action']=IN_UICE.'_document_download';
		$_G['require_export']=false;
	}
	
}elseif((got('add') || got('edit')) && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_add';

}elseif(got('review') && is_permitted(IN_UICE,'review')){
	$_G['action']=IN_UICE.'_review_list';

}elseif(is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_list';

}else{
	exit('no permission');
}
require 'controller/export.php';
?>