<?php
class User_model extends People_model{
	
	var $name;
	
	/**
	 * 当前用户直接或间接所在的所有组
	 * @var array(team_id=>team_name,...) 
	 */
	var $teams;
	var $group;
	
	static $fields=array(
		'name'=>'用户名',
		'alias'=>'别名',
		'password'=>'密码'
	);
	
	function __construct($uid=NULL){
		parent::__construct();
		
		$this->table='user';
		
		if(is_null($uid)){
			$uid=$this->session->userdata('user/id');
		}
		
		if($uid){
			$user=$this->fetch($uid);
			$this->id=$user['id'];
			$this->name=$user['name'];
			$this->group=explode(',',$user['group']);
		}
		
		$this->teams=$this->team->trace($this->id);

		//获取存在数据库中的用户配置项
		$this->db->from('user_config')
			->where('user',$this->id);
		
		$config=array_sub($this->db->get()->result_array(),'value','name');
		
		array_walk($config, function(&$value){
			$decoded=json_decode($value);
			if(!is_null($decoded)){
				$value=$decoded;
			}
		});
		
		$this->config->user=$config;

	}
	
	function getList(array $args=array()){
		
		$this->db->select('people.*')
			->join('people','user.id = people.id','inner')
			->where('people.company',$this->company->id)
			->where('people.display',true);
		
		$args+=array('display'=>false,'company'=>false);
		
		return parent::getList($args);
	}
	
	function add($data=array()){
		$user_id=parent::add($data);

		$data=array_intersect_key($data, self::$fields);
		
		$data['id']=$user_id;
		$data['company']=$this->company->id;

		$this->db->insert('user',$data);
		
		return $user_id;
	}
	
	function verify($username,$password){
		
		$username=$this->db->escape($username);
		$password=$this->db->escape($password);
		
		$this->db
			->from('user')
			->where('company',$this->company->id)
			->where("(name = $username OR alias = $username)",NULL,false)
			->where("(password = $password OR password IS NULL)",NULL,false);
				
		$user=$this->db->get()->row_array();
		
		if(empty($user)){
			return false;
	
		}else{
			return $user;
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
	
	function updatePassword($user_id,$new_password){
		
		return $this->db->update('user',array('password'=>$new_password),array('id'=>$user_id));
		
	}
	
	function updateUsername($user_id,$new_username){
		return $this->db->update('user',array('name'=>$new_username),array('id'=>$user_id));
	}
	
	/**
	 * 根据用户名或uid直接为其设置登录状态
	 */
	function sessionLogin($uid=NULL,$username=NULL){
		$this->db->select('user.id,user.`group`,user.username,staff.position')
			->from('user')
			->join('staff','user.id = staff.id','left');

		if(isset($uid)){
			$this->db->where('user.id',$uid);
		}
		elseif(!is_null($username)){
			$this->db->where('user.name',$username);
		}
		
		$user=$this->db->get()->row_array();
		
		if($user){
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
			if(empty($this->id)){
				return false;
			}
		}elseif(empty($this->group) || !in_array($check_type,$this->group)){
			return false;
		}

		return true;
	}
	
	function inTeam($team){
		if(array_key_exists($team, $this->teams)){
			return true;
		}
		else{
			return false;
		}
	}

	function generateNav(){
		
		$query="
			SELECT * FROM (
				SELECT * FROM nav
				WHERE (company_type is null or company_type = '{$this->company->type}')
					AND (company ={$this->company->id} OR company IS NULL)
					AND (team IS NULL OR team{$this->db->escape_int_array(array_keys($this->teams))})
				ORDER BY company_type DESC, company DESC, team DESC
			)nav_ordered
			GROUP BY href
			ORDER BY parent, `order`
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

}
?>