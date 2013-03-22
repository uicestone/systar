<?php
class User_model extends People_model{
	
	var $name;
	var $teams;
	var $group;
	
	static $fields=array(
		'name'=>'用户名',
		'alias'=>'别名',
		'group'=>'用户组',
		'password'=>'密码'
	);
	
	function __construct($uid=NULL){
		parent::__construct();
		$this->table='user';
		
		$this->load->model('team_model','team');
		
		if(is_null($uid)){
			$uid=$this->session->userdata('user/id');
		}
		
		if($uid){
			$user=$this->fetch($uid);
			$this->id=$user['id'];
			$this->name=$user['name'];
			$this->group=explode(',',$user['group']);
		}else{
			$this->id=0;
		}
		
		$session=$this->session->all_userdata();
		
		$this->teams=$this->team->traceByPeople($this->id);

		foreach($session as $key => $value){
			if(preg_match('/^user\/(.*?)$/', $key,$matches)){
				$this->$matches[1]=$value;
			}
		}

	}
	
	function add($data=array()){
		$data['type']='学生';
		$user_id=parent::add($data);

		$data['group']='candidate';
		$data=array_intersect_key($data, self::$fields);
		
		$data['id']=$user_id;
		$data['company']=$this->company->id;

		$this->db->insert('user',$data);
		
		return $user_id;
	}
	
	function verify($username,$password){
		$q_user="
			SELECT id,name,password,`group`,lastip,lastlogin,company
			FROM user 
			WHERE (name = '$username' OR alias='$username')
				AND (password = '$password' OR password IS NULL)
				AND company={$this->company->id}
			";
		
		$user=$this->db->query($q_user)->row_array();
		
		if(empty($user)){
			return false;
	
		}else{
			return $user;
		}
	}
	
	function check($username,$field='id',$show_error=true){
		//$data_type:id,array
		
		if(!$username){
			if($show_error){
				showMessage('请输入用户名','warning');
			}
			return -3;
		}
	
		$query="SELECT * FROM `user` WHERE company={$this->company->id} AND `username` = '{$username}'";
		$result=$this->db->query($query);
		$num_lawyers=$result->num_rows();
	
		if($num_lawyers==0){
			if($show_error){
				showMessage('没有这个用户：'.$username,'warning');
			}
			return -1;
			
		}else{
			$data=$result->row_array($result);
			if($field=='array' || is_null($field)){
				$return=$data;
			}else{
				$return=$data[$field];
			}
			return $return;
		}
	}
	
	function updateLoginTime(){
		$this->db->update('user',
			array('lastip'=>$this->session->userdata('ip_address'),
				'lastlogin'=>$this->date->now
			),
			array('id'=>$this->id,'company'=>$this->company->id)
		);
	}
	
	function updatePassword($user_id,$new_password=NULL,$new_username=NULL){
		$user_id=intval($user_id);
		
		if(isset($new_password)){
			return $this->db->update('user',array('password'=>$new_password),array('id'=>$user_id));
		}
		
		if(isset($new_username)){
			if($this->db->update('user',array('username'=>$new_username),array('id'=>$user_id)) && $this->db->affected_rows()){
				$this->output->message('成功修改用户名');
			}else{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 根据用户名或uid直接为其设置登录状态
	 */
	function sessionLogin($uid=NULL,$username=NULL){
		if(isset($uid)){
			$q_user="
				SELECT user.id,user.`group`,user.username,staff.position
				FROM user 
					LEFT JOIN staff ON user.id=staff.id 
				WHERE user.id=$uid
			";

		}elseif(!is_null($username)){
			$q_user="
				SELECT user.id,user.`group`,user.username,staff.position 
				FROM user 
					LEFT JOIN staff ON user.id=staff.id 
				WHERE user.username='$username'
			";
		}
		
		if($user=$this->db->query($q_user)->row_array()){
			$this->session->set_userdata('user/id', $user['id']);
			return true;
		}
		return false;
	}

	/**
	 * 登出当前用户
	 */
	function sessionLogout(){
		session_unset();
		session_destroy();
		$this->session->sess_destroy();

		if($this->company->ucenter){
			//生成同步退出代码
			echo uc_user_synlogout();
		}
	}

	/**
	 * 判断是否以某用户组登录
	 * $check_type要检查的用户组,NULL表示只检查是否登录
	 * $refresh_permission会刷新用户权限，只需要在每次请求开头刷新即可
	 */
	function isLogged($check_type=NULL){
		if(is_null($check_type)){
			if(empty($this->group)){
				return false;
			}
		}elseif(empty($this->group) || !in_array($check_type,$this->group)){
			return false;
		}

		return true;
	}

	function generateNav(){
		
		$query="
			SELECT * FROM (
				SELECT * FROM nav
				WHERE (company_type is null or company_type = '{$this->company->type}')
					AND (company ={$this->company->id} OR company IS NULL)
					AND (team IS NULL ".($this->teams?"OR team IN (".implode(',',$this->teams).")":'').")
				ORDER BY company_type DESC,company DESC,team DESC
			)nav_ordered
			GROUP BY href
			ORDER BY parent,`order`
		";
					
		$result=$this->db->query($query);
		
		$nav=array();
		
		foreach($result->result() as $row){
			if(is_null($row->parent)){
				$nav[0][]=$row;
			}else{
				$nav[$row->parent][]=$row;
			}
		}
		
		function generate($nav,$parent=0,$level=0){
		
			$out='<ul level="'.$level.'">';

			foreach($nav[$parent] as $nav_item){
				$out.='<li href="'.$nav_item->href.'">';
				if(isset($nav[$nav_item->id])){
					$out.='<span class="arrow"><img src="images/arrow_r.png" alt=">" /></span>';
				}
				$out.='<a href="'.$nav_item->href.'" '.(isset($nav[$nav_item->id])?'':'class="dink"').'>'.$nav_item->name.'</a>';
				if($nav_item->add_href){
					$out.='<a href="'.$nav_item->add_href.'" class="add"> <span style="font-size:12px;color:#CEDDEC">+</span></a>';
				}
				if(isset($nav[$nav_item->id])){
					$out.=generate($nav,$nav_item->id,$level+1);
				}
				$out.='</li>';

			}
			$out.='</ul>';
				
			return $out;
		}
		
		return generate($nav);
	}

	/**
	 * 调用uc接口发送用户信息
	 */
	function sendMessage($receiver,$message,$title='',$sender=NULL){
		if(is_null($sender)){
			$sender=$this->id;
		}
		if($CFG->item('ucenter')){
			uc_pm_send($sender,$receiver,$title,$message);
		}
	}

}
?>