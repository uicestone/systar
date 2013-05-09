<?php
class Polling extends SS_Controller{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		$new_messages=$this->message->getNewMessages();
		$this->output->setData($new_messages,'messages','html','.new-messages');
		
		if($new_messages){
			$this->output->setData("$.get('/message')",'dialog','script');
		}
	}
}
?>
