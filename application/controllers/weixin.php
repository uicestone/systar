<?php
class Weixin extends SS_Controller{
	function __construct() {
		$this->permission=true;
		parent::__construct();
	}
	
	function index(){
		
		//验证
		if($this->input->get('echostr')){
			//TODO 需要对来源进行鉴别
			$this->output->set_output($this->input->get('echostr'));
			$this->_validation();
		}
		
		//消息回复
		ENVIRONMENT==='development' && $GLOBALS["HTTP_RAW_POST_DATA"]='1';
		if(isset($GLOBALS["HTTP_RAW_POST_DATA"])){
			
			xml_parse_into_struct(xml_parser_create(), $GLOBALS["HTTP_RAW_POST_DATA"], $post);
			$post=array_sub($post,'value','tag');
			
			ENVIRONMENT==='development' && $post=array(
				'MSGTYPE'=>'text',
				'TOUSERNAME'=>'allstar',
				'FROMUSERNAME'=>'uicestone',
				'CONTENT'=>"陆海",
			);
			
			$this->load->addViewArrayData($post);

			$message='';
			
			$user=$this->people->getRow(array('has_profiles'=>array('weixin_openid'=>$post['FROMUSERNAME'])));

			//如果发件人不是已知用户，那么尝试一下将信息作为用户名密码登陆
			if(!$user){
				$user=array();
				$login=explode("\n",$post['CONTENT']);

				if(count($login)===2){
					$user=$this->user->verify($login[0],$login[1]);
				}

				//如果登陆成功，则保存openid今后不用再认证了
				if($user){
					$this->people->addProfile($user['id'],'weixin_openid',$post['FROMUSERNAME']);
					$message.=$user['name'].', 欢迎你回来'."\n";
				}
			}
			
			if($user){
				$this->user->__construct($user['id']);
			}

			//文本消息			
			if($post['MSGTYPE']==='text'){
				
				//如果是职员
				if($this->people->getRow(array('id'=>$this->user->id,'is_staff'=>true))){

					$people=array_sub($this->people->match($post['CONTENT']),'id');

					$data=$this->people->getList(array('id_in'=>$people,'get_profiles'=>true));
					
					array_walk($data,function(&$value){
						$value=array_merge($value,$value['profiles']);
						unset($value['profiles']);
					});

					foreach($data as $row){
						$message.=$row['name']."\n";
						foreach($row as $field_name => $field_value){
							if(in_array($field_name,array('id','type','name','abbreviation'))){
								continue;
							}
							$message.=$field_name.": \n  ".$field_value."\n";
						}
						$message.="\n\n";
					}
				}
				//响应客户请求
				else{
					$message="上海星瀚律师事务所 021-51096488\n上海市普陀区常德路1211号宝华大厦12楼 地铁七号线长寿路站";
				}
				
				$this->load->addViewData('message',$message);
				$this->load->view('weixin/reply_text');
			}
			
		}
	}
}

?>
