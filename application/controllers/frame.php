<?php
class Frame extends SS_Controller{
	function __construct(){
		parent::__construct();
		!defined('IN_UICE') && define('IN_UICE','frame');
	}
	
	function index(){
		global $_G;
		if(IN_UICE!='user' && !is_logged(NULL,true)){
			//对于非用户登录/登出界面，检查权限，弹出未登陆
			redirect('user/login','js',NULL,true);
		}
		
		$data=compact('_G');
		$this->load->view('head_frame',$data);
		$this->load->view('frame');
	}
}
?>