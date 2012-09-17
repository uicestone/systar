<?php
unset($_SESSION[IN_UICE]['post']);

db_delete($_G['actual_table']==''?IN_UICE:$_G['actual_table'],"uid='".$_SESSION['id']."' AND display=0");//删除本用户的误添加数据

if($_G['as_popup_window']){
	closeWindow();
}else{
	redirect((sessioned('last_list_action')?$_SESSION['last_list_action']:IN_UICE));
}
?>