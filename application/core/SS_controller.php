<?php
class SS_controller extends CI_Controller{
	function __construct(){
		parent::__construct();
	}
	
	/*
	 * 在每个add页面之前获得数据ID，插入新数据或者根据数据ID获得数据数组
	 */
	function getPostData($id,$callback=NULL,$generate_new_id=true,$db_table=NULL){
		global $_G;
		if(isset($id)){
			unset($_SESSION[IN_UICE]['post']);
			post(IN_UICE.'/id',intval($id));
		
		}elseif(is_null(post(IN_UICE.'/id'))){
			unset($_SESSION[IN_UICE]['post']);
		
			processUidTimeInfo(IN_UICE);
		
			if(is_a($callback,'Closure')){
				$callback();
			}
	
			if($generate_new_id){
				if(is_null($db_table)){
					if($_G['actual_table']!=''){
						$db_table=$_G['actual_table'];
					}else{
						$db_table=IN_UICE;
					}
				}
				post(IN_UICE.'/id',db_insert($db_table,post(IN_UICE)));
			}
			//如果$generate_new_id==false，那么必须在callback中获得post(IN_UICE/id)
		}
	
		if(!post(IN_UICE.'/id')){
			showMessage('获得信息ID失败','warning');
			exit;
		}
		
		post(IN_UICE,$this->model->fetch(post(IN_UICE.'/id')));
	}
}
?>