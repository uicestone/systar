<?php
class Message extends SS_Controller{
	
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		
		try{
			if($this->input->post('submit')==='send'){
				if(!$this->input->post('content')){
					throw new Exception('请填写消息内容');
				}
				$message_id=$this->message->send($this->input->post('content'), $this->input->post('receivers'));

				if($this->input->post('documents')){
					$this->message->addDocuments($message_id, $this->input->post('documents'));
				}
				
			}
			
			$dialogs=$this->message->getDialogList();

			$this->load->model('document_model','document');
			
			foreach($dialogs as $index => $dialog){
				$dialogs[$index]['last_message_documents']=$this->document->getList(array('message'=>$dialog['last_message']));
			}
			
			$this->load->addViewData('dialogs', $dialogs);

			$this->load->view('message/dialog');
			
			if(!$this->input->get('blocks') || $this->input->post('sidebar')){
				$this->load->view('message/sidebar',true,'sidebar');
			}
			
		}catch(Exception $e){
			if($e->getMessage()){
				$this->output->message($e->getMessage(),'warning');
			}
		}
	}

	function content($dialog_id){
		
		try{
			if($this->input->post('submit')==='send'){
				
				if(!$this->input->post('content')){
					throw new Exception('请填写消息内容');
				}
				
				$message_id=$this->message->sendByDialog($this->input->post('content'), $dialog_id);
				
				if($this->input->post('documents')){
					$this->message->addDocuments($message_id, $this->input->post('documents'));
				}
				
			}
			
			$dialog=$this->message->fetchDialog($dialog_id);

			$this->output->title='对话 '.$dialog['title'];

			$messages=$this->message->getList($dialog_id);
			
			$this->load->model('document_model','document');
			
			foreach($messages as $index => $message){
				$messages[$index]['documents']=$this->document->getList(array('message'=>$message['id']));
			}

			$this->load->addViewData('messages', $messages);

			$this->load->view('message/list');
			
			if(!$this->input->get('blocks') || $this->input->get('blocks')=='sidebar'){
				$this->load->view('message/content_sidebar',true,'sidebar');
			}
			
			$this->message->setDialogRead($dialog_id);
			
		}catch(Exception $e){
			if($e->getMessage()){
				$this->output->message($e->getMessage(),'warning');
			}
		}
	}
	
	function to($receiver){
		$dialog=$this->message->getDialog($this->user->id, $receiver);
		$this->content($dialog);
	}
	
	/**
	 * 将当前用户的某条消息设为已删除
	 * @param $message_id
	 */
	function delete($message_id){
		$this->message->updateMessageUser($message_id,$this->user->id,array('deleted'=>true));
		redirect('message/content/'.$this->input->post('dialog'));
	}
	
	function deleteDialogMessage($dialog_id){
		$this->message->deleteDialogMessage($dialog_id, $this->user->id);
		redirect('message');
	}
	
}
?>
