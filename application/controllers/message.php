<?php
class Message extends SS_Controller{
	
	var $section_title='消息';
	
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		
		if($this->input->post('submit')==='send'){
			$this->message->send($this->input->post('content'), $this->input->post('receivers'));
		}
		
		$dialogs=$this->message->getDialogList();
		
		$this->load->addViewData('dialogs', $dialogs);
		
		$this->load->view('message/dialog');
		$this->load->view('message/sidebar',true,'sidebar');
	}

	function content($dialog_id){
		
		if($this->input->post('submit')==='send'){
			$this->message->sendByDialog($this->input->post('content'), $dialog_id);
		}
		
		$dialog=$this->message->fetchDialog($dialog_id);
		
		$this->section_title='对话 '.$dialog['title'];
		
		$messages=$this->message->getList($dialog_id);
		
		$this->load->addViewData('messages', $messages);
		
		$this->load->view('message/list');
		$this->load->view('message/content_sidebar',true,'sidebar');
	}
	
	function to($receiver){
		$dialog=$this->message->getDialog($this->user->id, $receiver);
		$this->content($dialog);
	}
	
}
?>
