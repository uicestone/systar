<?php
class Message_model extends BaseItem_model{
	
	function __construct(){
		parent::__construct();
	}
	
	function add($content){
		$this->db->insert('message',array(
			'content'=>$content,
			'uid'=>$this->user->id,
			'time'=>$this->date->now
		));
		
		return $this->db->insert_id();
	}
	
	function fetchDialog($dialog_id){
		$this->db->select('dialog.*, dialog_user.title')
			->from('dialog')
			->join('dialog_user',"dialog.id = dialog_user.dialog AND dialog_user.user = {$this->user->id}")
			->where('dialog.id',$dialog_id);
		
		return $this->db->get()->row_array();
	}
	
	function createDialog($sender,$receiver,$last_message=NULL){
		$this->db->insert('dialog',array('company'=>$this->company->id,'users'=>2,'uid'=>$this->user->id,'time'=>$this->date->now,'last_message'=>$last_message));
		
		$dialog=$this->db->insert_id();
		
		$people_model=new People_model();
		
		$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$sender,'title'=>$people_model->fetch($receiver,'name')));
		$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$receiver,'title'=>$people_model->fetch($sender,'name')));
		
		return $dialog;
	}
	
	function getDialog($sender,$receiver){
		
		$sender=intval($sender);
		$receiver=intval($receiver);
		
		$query="
			SELECT d0.dialog
			FROM dialog_user d0 INNER JOIN dialog_user d1 USING (dialog)
				INNER JOIN dialog ON dialog.id = d0.dialog AND dialog.users = 2
			WHERE d0.user = $sender AND d1.user = $receiver
		";
		
		$row=$this->db->query($query)->row();
		
		if($row){
			return $row->dialog;
		}
		else{
			return $this->createDialog($sender, $receiver);
		}
	}
	
	function setDialogRead($dialog_id){
		
		$dialog_id=intval($dialog_id);
		
		$this->db->update('dialog_user',array('read'=>true),array('dialog'=>$dialog_id));
		
		$this->db->where("user = {$this->user->id} AND message IN (SELECT message FROM dialog_message WHERE dialog = $dialog_id)",NULL,false)
			->update('message_user',array('read'=>true));
	}
	
	function getDialogList(){
		$this->db->select('
				dialog.*,
				dialog_user.title,
				last_message.content AS last_message_content,last_message.uid AS last_message_author,last_message_author.name AS last_message_author_name,last_message.time AS last_message_time,
				dialog_user.read AS `read`
			')
			->from('dialog')
			->join('message last_message',"last_message.id = dialog.last_message",'inner')
			->join('people last_message_author',"last_message_author.id = last_message.uid",'inner')
			->join('dialog_user',"dialog.id = dialog_user.dialog AND dialog_user.user = {$this->user->id}",'inner')
			->order_by('last_message.id','DESC');
		
		return $this->db->get()->result_array();
	}
	
	function getList($dialog_id){
		$dialog_id=intval($dialog_id);
		$this->db->select('message.*, people.name AS author_name, message_user.read AS `read`')
			->from('message')
			->join('dialog_message',"dialog_message.message = message.id AND dialog_message.dialog = $dialog_id",'inner')
			->join('people','people.id = message.uid','inner')
			->join('message_user',"message_user.message = message.id AND message_user.user = {$this->user->id}",'inner')
			->order_by('message.id','desc');
		
		return $this->db->get()->result_array();
	}
	
	/**
	 * 根据会话id插入发送的消息
	 * @param $content 消息内容
	 * @param int $dialog 会话id
	 * @return dialog_message insert_id
	 */
	function sendByDialog($content,$dialog){
		
		$dialog=intval($dialog);
		
		$message=$this->add($content);
		
		$this->db->insert('dialog_message',array(
			'dialog'=>$dialog,
			'message'=>$message
		));
		
		$result_dialog_user=$this->db->select('user')
			->from('dialog_user')
			->where('dialog',$dialog)
			->where('user !=',$this->user->id)
			->get()->result_array();
		
		$message_user_batch=array(
			array('message'=>$message,'user'=>$this->user->id,'read'=>true,'deleted'=>false)
		);
		
		foreach($result_dialog_user as $row){
			$message_user_batch[]=array(
				'message'=>$message,
				'user'=>$row['user'],
				'read'=>false,
				'deleted'=>false
			);
		}
		
		$this->db->insert_batch('message_user',$message_user_batch);
		
		$this->db->update('dialog',array('last_message'=>$message),array('id'=>$dialog));
		
		//将收件人的会话标记为未读
		$this->db->update('dialog_user',array('read'=>false),array('dialog'=>$dialog,'user !='=>$this->user->id));
		
		return $message;
	}
	
	/**
	 * 当前用户向一个或多个用户发送消息
	 * 注意，只有不知道dialog的时候才调用此函数，否则使用sendByDialog
	 * @param $content
	 * @param array or int $user 接收消息的用户
	 */
	function send($content,$receiver){
		
		if(!is_array($receiver)){
			$receivers=array($receiver);
		}else{
			$receivers=$receiver;
		}
		
		$message=$this->add($content);
		
		//获得每个收件人和当前用户所在的2人会话
		$query="
			SELECT d0.dialog,d1.user
			FROM dialog_user d0 INNER JOIN dialog_user d1 USING (dialog)
				INNER JOIN dialog ON dialog.id = d0.dialog AND dialog.users = 2
			WHERE FALSE
		";
		
		foreach($receivers as $receiver){
			$receiver=intval($receiver);
			$query.=" OR (d0.user = {$this->user->id} AND d1.user = $receiver)";
		}
		
		$result_existed_dialogs=$this->db->query($query)->result_array();
		
		//获得已有会话的收件人
		$receivers_with_dialog=array_sub($result_existed_dialogs,'user');
		$existed_dialogs=array_sub($result_existed_dialogs,'dialog');
		
		if($existed_dialogs){
			$this->db->query("
				UPDATE dialog SET last_message = $message
				WHERE id IN (".implode(',',$existed_dialogs).")
			");
		}
		
		//获得不存在会话的收件人
		$receivers_without_dialog=array_diff($receivers,$receivers_with_dialog);
		
		$people_model=new People_model();
		
		foreach($receivers_without_dialog as $receiver){
			$this->db->insert('dialog',array('company'=>$this->company->id,'users'=>2,'uid'=>$this->user->id,'time'=>$this->date->now,'last_message'=>$message));
			$dialog=$this->db->insert_id();
			$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$receiver,'title'=>$this->user->name,'read'=>false));
			$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$this->user->id,'title'=>$people_model->fetch($receiver,'name'),'read'=>true));
		}
		
		$set=array();
		
		//现在所有的收件人和当前用户都有会话了，我们再获得一次所有会话
		$dialogs=array_sub($this->db->query($query)->result_array(),'dialog');
		
		//把新消息插入这些会话
		foreach($dialogs as $dialog){
			$set[]=array('dialog'=>$dialog,'message'=>$message);
		}
		
		$this->db->insert_batch('dialog_message', $set);
		
		$message_user_set=array(
			array('message'=>$message,'user'=>$this->user->id,'read'=>true,'deleted'=>false)
		);
		
		foreach($receivers as $receiver){
			$message_user_set[]=array('message'=>$message,'user'=>$receiver,'read'=>false,'deleted'=>false);
		}
		
		$this->db->insert_batch('message_user',$message_user_set);
		
		//将收件人的会话标记为未读
		$this->db->where_in('dialog',$dialogs)->where('user !=',$this->user->id)
			->update('dialog_user',array('read'=>false));
		
		return $message;
	}
	
	/**
	 * 向用户组发送消息
	 * 注意，这与向用户组中的多人群发是有区别的：
	 * sendToTeam发送的邮件发送对象是team，会话参与人是发件人和收件组（如果发件人也属于收件组，那么会话参与人仅包括收件组）
	 * 因此在这样的消息会话中回复的消息，发件人和组成员将都可看到
	 * @param $content
	 * @param array or int $team 接受消息的用户组
	 */
	function sendToTeam($content,$team){
		if(!is_array($team)){
			$teams=array($team);
		}
		
		$message=$this->add($content);
		
		$query="
			INSERT INTO dialog_message (dialog, message)
			SELECT d0.dialog, $message
			FROM dialog_team d0 INNER JOIN dialog_team d1 USING (dialog)
				INNER JOIN dialog ON dialog.id = d0.dialog AND dialog.teams = 2
			WHERE FALSE
		";
		
		foreach($teams as $team){
			$team=intval($team);
			$query.=" OR (d0.team = {$this->team->id} AND d1.team = $team)";
		}
		
		return $this->db->query($query);
	}
	
	/**
	 * 返回一个用户的未读信息数
	 * @param int $user_id
	 */
	function getNewMessages($user_id=NULL){
		if(is_null($user_id)){
			$user_id=$this->user->id;
		}else{
			$user_id=intval($user_id);
		}
		
		$this->db->where(array('user'=>$user_id,'read'=>false));
		
		$count = $this->db->count_all_results('message_user');
		
		if($count===0){
			return false;
		}else{
			return $count;
		}
	}
	
	function addDocuments($message_id,array $documents){
		$set=array();
		foreach($documents as $document){
			$set[]=array('message'=>$message_id,'document'=>$document);
		}
		
		return $this->db->insert_batch('message_document',$set);
	}
	
}
?>