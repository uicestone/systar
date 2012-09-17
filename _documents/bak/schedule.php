<?php
define('IN_UICE','schedule');
require 'config/config.php';

if(!is_logged())
	redirect('user.php?login','js',NULL,true);

if(is_posted('submit/cancel') && is_permitted(IN_UICE)){
	$_G['action']='misc_cancel';
	$_G['require_export']=false;
	
}elseif((got('add')||got('edit')) && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_add';

}elseif(got('readcalendar') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_readcalendar';
	$_G['require_export']=false;
	
}elseif(got('writecalendar') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_writecalendar';
	$_G['require_export']=false;
	
}elseif((got('list') || got('mine')) && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_list';
	if(is_posted('export_to_excel')){
		$_G['require_export']=false;
	}
	
}elseif(got('outplan') && is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_outplan';
	
}elseif(got('listwrite') && is_permitted(IN_UICE)){//日志列表写入评语/审核时间
	$_G['action']=IN_UICE.'_list_write';
	$_G['require_export']=false;

}elseif(is_permitted(IN_UICE)){
	$_G['action']=IN_UICE.'_calendar';

}

require 'controller/export.php';
?>