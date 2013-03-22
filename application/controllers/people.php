<?php
/**
 * 这是一个半抽象的控制器类
 * 它既可以被直接使用，如搜索不特定人People::match()
 * 也可以被其他控制器比如Client继承
 */
class People extends SS_Controller{
	
	var $form_validation_rules=array();
	
	var $list_args;
	
	var $section_title='人员';
		
	function __construct() {
		parent::__construct();
		
		$this->load->model('team_model', 'team');
		
		$this->form_validation_rules['relative'][]=array(
			'field'=>'relative_profiles[电子邮件]',
			'label'=>'电子邮件',
			'rules'=>'valid_email'
		);
	}
	
	/**
	 * 根据请求的字符串返回匹配的人员id，名称和类别
	 */
	function match(){

		$term=$this->input->post('term');
		
		$result=$this->people->match($term);

		$array=array();

		foreach ($result as $row){
			$array[]=array(
				'label'=>$row['name'].'    '.$row['type'],
				'value'=>$row['id']
			);
		}
		$this->output->data=$array;
	}
	
	/**
	 * 列表页
	 */
	function index(){
		
		$this->list_args=array(
			'abbreviation'=>array(
				'heading'=>'名称',
				'cell'=>array('data'=>'{abbreviation}','class'=>"ellipsis",'title'=>'{name}')
			),
			'phone'=>array('heading'=>'电话'),
			'email'=>array('heading'=>'电邮'),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->people,'getCompiledLabels'),'args'=>array('{id}')))
		);
		
		if($this->input->post('team')){
			option('search/team',$this->input->post('team'));
		}
		
		//监测有效的名称选项
		if($this->input->post('name')!==false && $this->input->post('name')!==''){
			option('search/name',$this->input->post('name'));
		}
		
		if(is_array($this->input->post('labels'))){
			
			if(is_null(option('search/labels'))){
				option('search/labels',array());
			}
			
			option('search/labels',array_trim($this->input->post('labels'))+option('search/labels'));
		}
		
		//点击了取消搜索按钮，则清空session中的搜索项
		if($this->input->post('submit')==='search_cancel'){
			option('search/labels',array());
			option('search/name',NULL);
			option('search/team',array());
		}
		
		//提交了搜索项，但搜索项中没有labels项，我们将session中搜索项的labels项清空
		if($this->input->post('submit')==='search' && $this->input->post('search/labels')===false){
			option('search/labels',array());
		}
		
		$table=$this->table->setFields($this->list_args)
			->setRowAttributes(array('hash'=>CONTROLLER.'/edit/{id}'))
			->setData($this->people->getList(option('search')))
			->generate();
		$this->load->addViewData('list', $table);
		
		if(file_exists(APPPATH.'/views/'.CONTROLLER.'/list'.EXT)){
			$this->load->view(CONTROLLER.'/list');
		}else{
			$this->load->view('list');
		}
		
		if(file_exists(APPPATH.'/views/'.CONTROLLER.'/list_sidebar'.EXT)){
			$this->load->view(CONTROLLER.'/list_sidebar',true,'sidebar');
		}else{
			$this->load->view('people/list_sidebar',true,'sidebar');
		}
		
	}
	
	/**
	 * 添加入口
	 * 将立即跳转
	 * @TODO存在无法后退，容易造成数据库垃圾的问题
	 */
	function add(){
		$this->people->id=$this->people->add();
		$this->edit($this->people->id);
		redirect('#'.CONTROLLER.'/edit/'.$this->people->id);
	}
	
	/**
	 * 查看/编辑页面
	 */
	function edit($id){

		$this->people->id=$id;
		
		$this->load->model('staff_model','staff');
		$this->load->model('cases_model','cases');

		try{
			$people=array_merge($this->people->fetch($id),$this->input->sessionPost('people'));
			$labels=$this->people->getLabels($this->people->id);
			$profiles=array_sub($this->people->getProfiles($this->people->id),'content','name');

			if(!$people['name'] && !$people['abbreviation']){
				
				$this->section_title='未命名';
			}else{
				$this->section_title=$people['abbreviation']?$people['abbreviation']:$people['name'];
			}

			$available_options=$this->people->getAllLabels();
			$profile_name_options=$this->people->getProfileNames();

			$this->subList('relative');
			$this->subList('profile');
			$this->subList('case');

			if($people['staff']){
				$people['staff_name']=$this->staff->fetch($people['staff'],'name');
			}

			$this->load->addViewArrayData(compact('controller','people','labels','profiles','available_options','profile_name_options'));

			if($this->input->post('character') && in_array($this->input->post('character'),array('个人','单位'))){
				post('people/character', $this->input->post('character'));
			}

			$this->load->view('people/edit');
			$this->load->view('people/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}
	}

	/**
	 * 提交处理
	 */
	function submit($submit,$id,$button_id=NULL){

		$this->people->id=$id;
		
		$people=array_merge($this->people->fetch($id),$this->input->sessionPost('people'));
		
		$this->load->library('form_validation');
		
		try{
			
			if(isset($this->form_validation_rules[$submit])){
				$this->form_validation->set_rules($this->form_validation_rules[$submit]);
				if($this->form_validation->run()===false){
					$this->output->message(validation_errors(),'warning');
					throw new Exception;
				}
			}
		
			if($submit=='cancel'){
				unset($_SESSION[CONTROLLER]['post'][$this->people->id]);
				$this->output->status='close';
			}

			elseif($submit=='people'){
				$labels=$this->input->sessionPost('labels');
				$profiles=$this->input->sessionPost('profiles');

				if($people['character'] == '单位' && $people['abbreviation'] == ''){
					//单位简称必填
					$this->output->message('请填写单位简称','warning');
					throw new Exception;
				}
				
				if($people['character']!='单位' && !$people['gender']){
					//个人，则性别必填
					$this->output->message('选择性别','warning');
					throw new Exception;
				}
				
				if(!isset($labels['类型'])){
					$this->output->message('请选择客户类型','warning');
					throw new Exception;
				}
				
				$this->people->update($this->people->id,post('people'));
				$this->people->updateLabels($this->people->id,$labels);
				$this->people->updateProfiles($this->people->id,$profiles);

				unset($_SESSION[CONTROLLER]['post'][$this->people->id]);
				$this->output->status='close';
			}

			elseif($submit=='relative'){
				
				$relative=$this->input->sessionPost('relative');
				
				if(!isset($relative['relation'])){
					$this->output->message('请选择相关人与客户关系','warning');
					throw new Exception;
				}
				
				if(!$relative['id']){
					$profiles=$this->input->sessionPost('relative_profiles');
					
					if(count($profiles)==0){
						$this->output->message('请至少输入一种联系方式','warning');
						throw new Exception;
					}
					
					$relative+=array(
						'type'=>'客户',
						'abbreviation'=>$relative['name'],
						'character'=>isset($relative['character']) && $relative['character'] == '单位' ? '单位' : '个人',
						'profiles'=>$profiles,
						'labels'=>array('类型'=>'潜在客户')
					);
					$relative['id']=$this->people->add($relative);
					$this->output->message('新客户 <a href="#'.CONTROLLER.'/edit/' . $relative['id'] . '">' . $relative['name'] . ' </a>已经添加');
				}else{
					$this->output->message('系统中已经存在 ' . $relative['name'] . '，已自动识别并添加');
				}

				$this->people->addRelationship($this->people->id,$relative['id'],$relative['relation']);

				$this->output->setData($this->subList('relative',$this->people->id));
				
				unset($_SESSION[CONTROLLER]['post'][$this->people->id]['relative']);

			}

			elseif($submit=='remove_relative'){
				$this->people->removeRelationship($this->people->id,$button_id);
				$this->output->setData($this->subList('relative',$this->people->id));
			}

			elseif($submit=='profile'){
				$profile=$this->input->sessionPost('profile');
				
				if(!$profile['name']){
					$this->output->message('请选择资料项名称','warning');
					throw new Exception;
				}
				
				$this->people->addProfile($this->people->id,$profile['name'],$profile['content'],$profile['comment']);
				
				$this->output->setData($this->subList('profile',$this->people->id));
				
				unset($_SESSION[CONTROLLER]['post'][$this->people->id]['profile']);
			}

			elseif($submit=='remove_profile'){
				$this->people->removeProfile($this->people->id,$button_id);
				$this->output->setData($this->subList('profile',$this->people->id));
			}
			
			elseif($submit=='changetype'){
				$this->people->update($this->people->id,array('type'=>$this->input->post('type')));
			}
			
			if(is_null($this->output->status)){
				$this->output->status='success';
			}
			
		}catch(Exception $e){
			$this->output->status='fail';
		}
	}

	/**
	 * 查看/编辑页中的子列表
	 */
	function subList($item,$people_id=false){

		if($people_id){
			$people=$this->people->fetch($people_id);
		}

		//相关人
		if($item=='relative'){
			$field=array(
				'name'=>array('heading'=>'名称','cell'=>'{name}<button type="submit" id="{id}" name="submit[remove_relative]" class="hover">删除</button>'), 
				'phone'=>array('heading'=>'电话'), 
				'email'=>array('heading'=>'电邮'), 
				'relation'=>array('heading'=>'关系')
			);
			
			$list=$this->table->setFields($field)
				->setRowAttributes(array('hash'=>CONTROLLER.'/edit/{reltive}'))
				->setData($this->people->getRelatives($this->people->id))
				->generate();

		}
		//资料项
		elseif($item=='profile'){
			$field=array(
				'name'=>array('heading'=>'名称','cell'=>'{name}<button type="submit" id="{id}" name="submit[remove_profile]" class="hover">删除</button>'), 
				'content'=>array('heading'=>'内容', 'eval'=>true, 'cell'=>"
					if('{name}'=='电子邮件'){
						return '<a href=\"mailto:{content}\" target=\"_blank\">{content}</a>';
					}else{
						return '{content}';
					}
				", 'orderby'=>false), 
				'comment'=>array('heading'=>'备注', 'orderby'=>false)
			);
			
			$list=$this->table->setFields($field)
				->setData($this->people->getProfiles($this->people->id))
				->generate();

		}
		//相关案件
		elseif($item=='case'){
			$field=array(
				'num'=>array(
					'heading'=>'案号'
				),
				'case_name'=>array(
					'heading'=>'案名'
				), 
				'lawyers'=>array(
					'heading'=>'主办律师' 
				)
			);
			$list=$this->table->setFields($field)
				->setRowAttributes(array('hash'=>'cases/edit/{id}'))
				->setData($this->cases->getListByPeople($this->people->id))
				->generate();
		}
		
		if(!$people_id){//没有指定$people_id，是在edit方法内调用
			$this->load->addViewData($item.'_list', $list);
		}else{
			return array('selector'=>'.item[name="'.$item.'"]>.contentTable','content'=>$list,'type'=>'html','method'=>'replace','content_name'=>'content-table');
		}

	}
	
}
?>
