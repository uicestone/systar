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
	
	function getDialogList(){
		$this->db->select('
				dialog.*,
				dialog_user.title,
				message.content AS last_message,message.uid AS last_message_author,people.name AS last_message_author_name,message.time AS last_message_time')
			->from('dialog')
			->join('message',"message.id = dialog.last_message",'INNER')
			->join('people',"people.id = message.uid")
			->join('dialog_user',"dialog.id = dialog_user.dialog AND dialog_user.user = {$this->user->id}")
			->order_by('message.id','DESC');
		
		return $this->db->get()->result_array();
	}
	
	function getList($dialog_id){
		$dialog_id=intval($dialog_id);
		$this->db->select('message.*,people.name AS author_name')
			->from('message')
			->join('dialog_message',"dialog_message.message = message.id AND dialog_message.dialog = $dialog_id")
			->join('people','people.id = message.uid','INNER')
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
		
		$message=$this->add($content);
		
		$this->db->insert('dialog_message',array(
			'dialog'=>intval($dialog),
			'message'=>$message
		));
		
		return $this->db->insert_id();
	}
	
	/**
	 * 当先用户向一个或多个用户发送消息
	 * 注意，只有不知道dialog的时候才调用此函数，否则使用sendByDialog
	 * @param $content
	 * @param array or int $user 接收消息的用户
	 */
	function send($content,$user){
		
		if(!is_array($user)){
			$users=array($user);
		}else{
			$users=$user;
		}
		
		$message=$this->add($content);
		
		$query="
			SELECT d0.dialog,d1.user
			FROM dialog_user d0 INNER JOIN dialog_user d1 USING (dialog)
				INNER JOIN dialog ON dialog.id = d0.dialog AND dialog.users = 2
			WHERE FALSE
		";
		
		foreach($users as $user){
			$user=intval($user);
			$query.=" OR (d0.user = {$this->user->id} AND d1.user = $user)";
		}
		
		$users_with_dialog=array_sub($this->db->query($query)->result_array(),'user');
		
		if($users_with_dialog){
			$this->db->query("
				UPDATE dialog SET last_message = $message
				WHERE id IN (".implode(',',$users_with_dialog).")
			");
		}
		
		$users_without_dialog=array_diff($users,$users_with_dialog);
		
		$this->load->model('people_model','people');
		
		foreach($users_without_dialog as $user){
			$this->db->insert('dialog',array('company'=>$this->company->id,'users'=>2,'uid'=>$this->user->id,'time'=>$this->date->now,'last_message'=>$message));
			$dialog=$this->db->insert_id();
			$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$user,'title'=>$this->user->name));
			$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$this->user->id,'title'=>$this->people->fetch($user,'name')));
		}
		
		$set=array();
		$dialogs=array_sub($this->db->query($query)->result_array(),'dialog');
		foreach($dialogs as $dialog){
			$set[]=array('dialog'=>$dialog,'message'=>$message);
		}
		
		return $this->db->insert_batch('dialog_message', $set);
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
}
?>