<?php
require_once APPPATH.'/models/people_model.php';
class User_model extends People_model{
	
	var $table='user';
	
	var $id;
	var $name;
	var $group=array();
	var $permission=array();
	
	var $child;
	var $manage_class;
	var $teacher_group;
	var $course;
	var $class;
	var $class_name;
	var $grade;
	var $grade_name;
	
	static $fields=array(
		'name'=>'用户名',
		'alias'=>'别名',
		'group'=>'用户组',
		'password'=>'密码'
	);
	
	function __construct($uid=NULL){
		parent::__construct();
		
		if($uid){
			$user=$this->fetch($uid);
			$this->session->set_userdata('user/id', $user['id']);
			$this->session->set_userdata('user/name', $user['name']);

			$user['group']=explode(',',$user['group']);
			$this->session->set_userdata('user/group', $user['group']);
		}
		
		$session=$this->session->all_userdata();
		foreach($session as $key => $value){
			if(preg_match('/^user\/(.*?)$/', $key,$matches)){
				$this->$matches[1]=$value;
			}
		}

		$this->preparePermission();
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
			$this->session->set_userdata('user/group', explode(',',$user['group']));
			$this->session->set_userdata('user/name', $user['username']);
			$this->session->set_userdata('user/position', $user['position']);
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

	/**
	 * 根据当前用户组，将数据库中controller,permission两表中的用户权限读入$this->user->permission
	 */
	function preparePermission(){

		$query="
			SELECT
				controller.name AS controller,
				IF(permission.ui_name<>'', permission.ui_name, controller.ui_name) AS controller_name,
				IF(permission.discription IS NOT NULL, permission.discription, controller.discription) AS discription,
				controller.add_action,
				permission.controller, permission.method, permission.display_in_nav AS display
			FROM controller LEFT JOIN permission ON controller.name=permission.controller 
			WHERE permission.company={$this->company->id}
				AND controller.is_on=1
		";
		
		if($this->group){
			$query.="AND (".db_implode($this->group, $glue = ' OR ','permission.group').") ";
		}else{
			$query.="AND FALSE";
		}
				
		$query.="
			GROUP BY permission.controller,permission.method
			ORDER BY controller.order, permission.order
		";

		$result_array=$this->db->query($query)->result_array();

		$permission=array();
		foreach($result_array as $a){
			if(!isset($permission[$a['controller']])){
				$permission[$a['controller']]=array();
			}
			if($a['method']==''){
				//一级菜单
				$permission[$a['controller']]
				=array_replace_recursive($permission[$a['controller']],array('_controller_name'=>$a['controller_name'],'_add_action'=>$a['add_action'],'_display'=>$a['display']));
			}else{
				//二级菜单
				$permission[$a['controller']][$a['method']]=array('_controller_name'=>$a['controller_name'],'_display'=>$a['display']);
			}
		}
		$this->permission=$permission;
	}

	/**
	 * 根据已保存的$_SESSION['permission']判断权限
	 * $action未定义时，只验证是否具有访问当前controller的权限
	 */
	function isPermitted($controller,$action=NULL){
		if(isset($this->permission[$controller])){
			if(is_null($action)){
				return true;
			}else{
				return isset($this->permission[$controller][$action])?true:false;
			}
		}else{
			return false;
		}
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