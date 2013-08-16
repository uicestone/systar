<?php
class Message_model extends Object_model{
	
	function __construct(){
		parent::__construct();
	}
	
	function add($content){
		$this->db->insert('message',array(
			'content'=>$content,
			'uid'=>$this->user->id,
			'time'=>time()
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
		$this->db->insert('dialog',array('company'=>$this->company->id,'users'=>2,'uid'=>$this->user->id,'time'=>time(),'last_message'=>$last_message));
		
		$dialog=$this->db->insert_id();
		
		$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$sender,'title'=>$this->people->fetch($receiver,'name')));
		$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$receiver,'title'=>$this->people->fetch($sender,'name')));
		
		return $dialog;
	}
	
	function getDialog($sender,$receiver){
		
		$sender=intval($sender);
		$receiver=intval($receiver);
		
		$this->db->select('d0.dialog')
			->from('dialog_user d0')
			->join('dialog_user d1','d0.dialog = d1.dialog','inner')
			->join('dialog','dialog.id = d0.dialog AND dialog.users = 2')
			->where(array('d0.user'=>$sender,'d1.user'=>$receiver));
		
		$row=$this->db->get()->row();
		
		if($row){
			return $row->dialog;
		}
		else{
			return $this->createDialog($sender, $receiver);
		}
	}
	
	/**
	 * 将当前用户的一个会话和其中的所有信息标记为已读
	 * @param int $dialog_id
	 */
	function setDialogRead($dialog_id){
		
		$dialog_id=intval($dialog_id);
		
		$this->db->update('dialog_user',array('read'=>true),array('dialog'=>$dialog_id,'user'=>$this->user->id));
		
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
			->join('dialog_user',"dialog.id = dialog_user.dialog AND dialog_user.hidden=0 AND dialog_user.user = {$this->user->id}",'inner')
			->order_by('last_message.id','DESC');
		
		return $this->db->get()->result_array();
	}
	
	function getList($dialog_id){
		$dialog_id=intval($dialog_id);
		$this->db->select('message.*, people.name AS author_name, message_user.read AS `read`')
			->from('message')
			->join('dialog_message',"dialog_message.message = message.id AND dialog_message.dialog = $dialog_id",'inner')
			->join('people','people.id = message.uid','inner')
			->join('message_user',"message_user.message = message.id AND message_user.user = {$this->user->id} AND message_user.deleted=0",'inner')
			->order_by('message.id','desc');
		
		return $this->db->get()->result_array();
	}
	
	/**
	 * 根据会话id插入发送的消息
	 * @param string $content 消息内容
	 * @param int $dialog 会话id
	 * @return message.id
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
		
		//将收件人的会话标记为未读且取消隐藏
		$this->db->update('dialog_user',array('read'=>false,'hidden'=>false),array('dialog'=>$dialog,'user !='=>$this->user->id));
		
		return $message;
	}
	
	/**
	 * 当前用户向一个或多个用户或组发送消息
	 * 注意，只有不知道dialog的时候才调用此函数，否则使用sendByDialog
	 * @param $content
	 * @param array or int $user 接收消息的用户或组
	 * @return message.id
	 * @todo 非本组成员向本组发送消息，应当先经过组长审批
	 * @todo 已经存在的组对话，外来发件人不会自动加入会话
	 */
	function send($content,$receivers){
		
		if(!is_array($receivers)){
			$receivers=array($receivers);
		}
		
		array_walk($receivers,function($value){
			$value=intval($value);
		});
		
		//根据user过滤收件人，并且去除本人
		$receivers=array_column(
			$this->db->select('id')->from('user')
				->where_in('id',$receivers)
				->where('id !=',$this->user->id)
				->get()->result_array()
		,'id');

		if(empty($receivers)){
			return;
		}
		
		$message=$this->add($content);
		
		$team_receivers=array_column($this->db->select('id')->from('team')->where_in('id',$receivers)->get()->result_array(),'id');
		
		$personal_receivers=array_diff($receivers,$team_receivers);
		
		//找出组收件人，获得其对话。由于一个组只可能出现在一个会话中，因此单找即可
		$result_existed_team_dialogs=$this->db->select('dialog,user')
			->from('dialog_user')
			->where_in('user',$team_receivers)
			->get()->result_array();
		
		$team_dialogs=array_column($result_existed_team_dialogs,'dialog');
		$team_receivers_with_dialog=array_column($result_existed_team_dialogs,'user');
		
		//获得每个非组收件人和当前用户所在的2人会话
		$this->db->select('d0.dialog, d1.user')
			->from('dialog_user d0')
			->join('dialog_user d1','d0.dialog = d1.dialog','inner')
			->join('dialog','dialog.id = d0.dialog AND dialog.users=2','inner')
			->where('FALSE',NULL,false);
		
		foreach($personal_receivers as $personal_receiver){
			$this->db->or_where("(d0.user = {$this->user->id} AND d1.user = $personal_receiver)",NULL,false);
		}
		
		$result_existed_personal_dialogs=$this->db->get()->result_array();
		$personal_dialogs=array_column($result_existed_personal_dialogs,'dialog');
		$personal_receivers_with_dialog=array_column($result_existed_personal_dialogs,'user');
		
		//获得不存在会话的收件人
		$personal_receivers_without_dialog=array_diff($personal_receivers,$personal_receivers_with_dialog);
		$team_receivers_without_dialog=array_diff($team_receivers,$team_receivers_with_dialog);
		
		//创建非组收件人的会话
		foreach($personal_receivers_without_dialog as $personal_receiver_without_dialog){
			$this->db->insert('dialog',array('company'=>$this->company->id,'users'=>2,'uid'=>$this->user->id,'time'=>time(),'last_message'=>$message));
			$dialog=$this->db->insert_id();
			$personal_dialogs[]=$dialog;
			$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$personal_receiver_without_dialog,'title'=>$this->user->name,'read'=>false));
			$this->db->insert('dialog_user',array('dialog'=>$dialog,'user'=>$this->user->id,'title'=>$this->people->fetch($personal_receiver_without_dialog,'name'),'read'=>true));
		}
		
		//创建组收件人的会话
		foreach($team_receivers_without_dialog as $team_receiver_without_dialog){
			$team_members_result=$this->db->select('relative')->from('object_relationship')->where('object',$team_receiver_without_dialog)->get()->result_array();
			$team_members=array_column($team_members_result,'relative');

			$this->db->insert('dialog',array('company'=>$this->company->id,'users'=>count($team_members),'uid'=>$this->user->id,'time'=>time(),'last_message'=>$message));
			$dialog=$this->db->insert_id();
			
			$team_dialogs[]=$dialog;
			
			$set=array(
				array('dialog'=>$dialog,'user'=>$team_receiver_without_dialog,'title'=>$this->group->fetch($team_receiver_without_dialog,'name'))
			);
			
			foreach($team_members as $team_member){
				$set[]=array('dialog'=>$dialog,'user'=>$team_member,'title'=>$this->group->fetch($team_receiver_without_dialog,'name'));
			}
			
			if(!in_array($this->user->id,$team_members)){
				$set[]=array('dialog'=>$dialog,'user'=>$this->user->id,'title'=>$this->group->fetch($team_receiver_without_dialog,'name'));
			}
			
			$this->db->insert_batch('dialog_user',$set);
		}
		
		$set=array();
		
		$dialogs=$personal_dialogs+$team_dialogs;
		
		//把新消息插入这些会话
		foreach($dialogs as $dialog){
			$set[]=array('dialog'=>$dialog,'message'=>$message);
		}
		
		$this->db->insert_batch('dialog_message', $set);
		
		$this->db->where_in('id',$dialogs)->update('dialog',array('last_message'=>$message));
		$this->db->where_in('dialog',$dialogs)->update('dialog_user',array('read'=>false));
		
		$this->db->set('hidden',false)->where_in('dialog',$dialogs)->update('dialog_user');
		$set=$this->db->select('user')->from('dialog_user')->where_in('dialog',$dialogs)->get()->result_array();
		foreach($set as $key=>$val){
			$set[$key]+=array('message'=>$message,'read'=>false,'deleted'=>false);
		}
		$this->db->insert_batch('message_user',$set);
		
		//将发件人的会话和消息标记为已读
		$this->db->where_in('dialog',$dialogs)->where('user',$this->user->id)
			->update('dialog_user',array('read'=>true));
		$this->db->where(array('message'=>$message,'user'=>$this->user->id))
			->update('message_user',array('read'=>true));
		
		return $message;
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
	
	function getNewMessagesContent($user_id=NULL){
		if(is_null($user_id)){
			$user_id=$this->user->id;
		}else{
			$user_id=intval($user_id);
		}
		
		$this->db->select('message.id, message.content, message.uid AS author, author.name as author_name, message.time')
			->from('message')
			->join('message_user','message_user.message = message.id','inner')
			->join('people author','message.uid = author.id','inner')
			->where(array('message_user.user'=>$user_id,'read'=>false));
		
		return $this->db->get()->result_array();
	}
	
	function addDocuments($message_id,array $documents){
		$set=array();
		foreach($documents as $document){
			$set[]=array('message'=>$message_id,'document'=>$document);
		}
		
		return $this->db->insert_batch('message_document',$set);
	}
	
	/**
	 * 更新用户消息关联
	 * @param int $message
	 * @param int $user
	 * @param array $data
	 */
	function updateMessageUser($message, $user, array $data){
		$this->db->update('message_user', $data, array('message'=>$message,'user'=>$user));
		return $this->db->affected_rows();
	}
	
	/**
	 * 将一个用户的一个会话下的所有消息标记为删除，并将会话标记为隐藏
	 * @param int $dialog
	 * @param int $user
	 */
	function deleteDialogMessage($dialog, $user){
		$this->db->where("message IN (SELECT message FROM dialog_message WHERE dialog{$this->db->escape_int_array($dialog)})",NULL,false)
			->where('user',$user)
			->update('message_user',array('deleted'=>1));
		
		$this->db->update('dialog_user',array('hidden'=>true),array('dialog'=>$dialog,'user'=>$user));
	}
	
}
?>