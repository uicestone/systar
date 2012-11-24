<?php
class User_model extends SS_Model{
	
	var $id;
	var $name;
	var $group;
	var $permission;
	
	var $child;
	var $manage_class;
	var $teacher_group;
	var $course;
	var $class;
	var $class_name;
	var $grade;
	var $grade_name;
	
	function __construct(){
		parent::__construct();
		$session=$this->session->all_userdata();
		foreach($session as $key => $value){
			if(preg_match('/^user\/(.*?)$/', $key,$matches)){
				$this->$matches[1]=$value;
			}
		}
	}
	
	function verify($username,$password){
		$q_user="
			SELECT id,name,password,`group`,lastip,lastlogin,company
			FROM user 
			WHERE (name = '{$this->input->post('username')}' OR alias='{$this->input->post('username')}')
				AND (password = '{$this->input->post('password')}' OR password IS NULL)
				AND company={$this->config->item('company/id')}
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
	
		$query="SELECT * FROM `user` WHERE company={$this->config->item('company/id')} AND `username` = '{$username}'";
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
				'lastlogin'=>$this->config->item('timestamp')
			),
			array('id'=>$this->id,'company'=>$this->config->item('company/id'))
		);
	}
	
	function edit($user_id,$new_password=NULL,$new_username=NULL){
		if(isset($new_password)){
			if($this->db->update('user',array('password'=>$new_password),"id = $user_id")){
				showMessage('成功修改密码');
			}else{
				return false;
			}
		}
		
		if(isset($new_username)){
			if($this->db->update('user',array('username'=>$new_username),"id = $user_id") && $this->db->affected_rows()){
				showMessage('成功修改用户名');
			}else{
				return false;
			}
		}
		
		return true;
	}
	
	function getRegionByIdcard($idcard){
		$query="SELECT name FROM user_idcard_region WHERE num = '".substr($idcard,0,6)."'";
		$region = $this->db->query($query)->row()->name;
		if($region){
			return $region;
		}else{
			return false;
		}
	}
	
	function verifyIdCard($idcard){
		if(!is_string($idcard) || strlen($idcard)!=18){
			return false;
		}
		$sum=$idcard[0]*7+$idcard[1]*9+$idcard[2]*10+$idcard[3]*5+$idcard[4]*8+$idcard[5]*4+$idcard[6]*2+$idcard[7]+$idcard[8]*6+$idcard[9]*3+$idcard[10]*7+$idcard[11]*9+$idcard[12]*10+$idcard[13]*5+$idcard[14]*8+$idcard[15]*4+$idcard[16]*2;
		$mod = $sum % 11;
		$vericode_dic=array(1, 0, 'x', 9, 8, 7, 6, 5, 4, 3, 2);
		if($vericode_dic[$mod] == strtolower($idcard[17])){
			return true;
		}
	}
	
	function getGenderByIdcard($idcard){
		if(is_string($idcard) && strlen($idcard)==18){
			return $idcard[16] % 2 == 1 ? '男' : '女';
		}else{
			return false;
		}
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

		if($this->config->item('company/ucenter')){
			//生成同步退出代码
			echo uc_user_synlogout();
		}
	}

	/**
	 * 判断是否以某用户组登录
	 * $check_type要检查的用户组,NULL表示只检查是否登录
	 * $refresh_permission会刷新用户权限，只需要在每次请求开头刷新即可
	 */
	function isLogged($check_type=NULL,$refresh_permission=false){
		if(is_null($check_type)){
			if(!isset($this->group)){
				return false;
			}
		}elseif(!isset($this->group) || !in_array($check_type,$this->group)){
			return false;
		}

		if($refresh_permission){
			$this->preparePermission();
			if($this->config->item('company/ucenter')){
				$this->session->set_userdata('new_messages', uc_pm_checknew($this->id));
			}
		}

		return true;
	}

	/**
	 * 根据当前用户组，将数据库中affair,group两表中的用户权限读入$_SESSION['permission']
	 */
	function preparePermission(){
		//准备权限参数，写入session

		$q_affair="
			SELECT
				affair.name AS affair,
				IF(group.affair_ui_name<>'', group.affair_ui_name, affair.ui_name) AS affair_name,
				affair.add_action,affair.add_target,
				`group`.action AS `action`, group.display_in_nav AS display
			FROM affair LEFT JOIN `group` ON affair.name=`group`.affair 
			WHERE group.company={$this->config->item('company/id')}
				AND affair.is_on=1
				AND (".db_implode($this->group, $glue = ' OR ','group.name').") 
			GROUP BY affair,action
			ORDER BY affair.order,group.order
		";

		$result_array=$this->db->query($q_affair)->result_array();

		$permission=array();
		foreach($result_array as $a){
			if(!isset($permission[$a['affair']])){
				$permission[$a['affair']]=array();
			}
			if($a['action']==''){
				//一级菜单
				$permission[$a['affair']]
				=array_replace_recursive($permission[$a['affair']],array('_affair_name'=>$a['affair_name'],'_add_action'=>$a['add_action'],'_add_target'=>$a['add_target'],'_display'=>$a['display']));
			}else{
				//二级菜单
				$permission[$a['affair']][$a['action']]=array('_affair_name'=>$a['affair_name'],'_display'=>$a['display']);
			}
		}
		$this->session->set_userdata('user/permission', $permission);
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