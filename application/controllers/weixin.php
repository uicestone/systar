<?php
class Weixin extends SS_Controller{
	function __construct() {
		$this->permission=true;
		parent::__construct();
	}
	
	function index(){
		
		//验证
		if($this->input->get('echostr')){
			//TODO 需要对来源进行鉴别
			$this->output->set_output($this->input->get('echostr'));
		}
		
		$this->load->addViewArrayData($this->input->post());
		
		//消息推送
		if($this->input->post('FromUserName')){
			//文本消息
			if($this->input->post('MsgType')==='text'){
				$message=$this->input->post('content');
				$this->load->view('weixin/reply_text');
			}
		}
		
	}
	
}

?>
