<?php
class Email extends SS_Controller{
	function __construct() {
		parent::__construct();
		$this->load->library('email');
		$config=array(
			'protocol'=>'smtp',
			'smtp_host'=>'127.0.0.1',
			'smtp_user'=>'lawyer@lawyerstars.com',
			'smtp_pass'=>'1218xinghan',
			'mailtype'=>'html',
			'validate'=>true
		);
		$this->email->initialize($config);
	}
	
	function index(){
		$this->email->from('lawyer@lawyerstars.com','星瀚律师事务所');
		$this->email->subject('测试邮件1');
		$this->email->message('<h1>hello!</h1>');
		
		$this->email->to('uicestone@gmail.com');
		$this->email->send();
		echo $this->email->_debug_msg;
		
		$this->email->to('uicestone@qq.com');
		$this->email->send();
		echo $this->email->_debug_msg;
	}
}
?>
