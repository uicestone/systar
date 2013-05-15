<?php
class Polling extends SS_Controller{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		$new_messages=$this->message->getNewMessages();
		$this->output->setData($new_messages,'messages','html','.new-messages');
		
		if($new_messages){
			$this->output->setData("$.post('/message',{blocks:'content'})",'dialog','script');
			$this->output->setData($this->message->getNewMessagesContent(),'notifications');
		}else{
			$this->output->setData(array(),'notifications');
		}
	}
}
?>
