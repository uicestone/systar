<?php
class Client extends SS_Controller{
	function __construct(){
		$this->default_method='lists';
		parent::__construct();
	}

	function potential(){
		$this->lists('potential');
	}

	function lists($method=NULL){
		$this->output->setData('客户', 'name');

		$field=array(
			'abbreviation'=>array(
				'heading'=>'名称',
				'cell'=>array('data'=>'{abbreviation}',	'class'=>"ellipsis",'title'=>'{name}')
			),
			'phone'=>array('heading'=>'电话','cell'=>array('class'=>'ellipsis','title'=>'{phone}')),
			'address'=>array(
				'heading'=>array('data'=>'地址','width'=>'240px'),
				'cell'=>array('class'=>'ellipsis','title'=>'{address}')
			),
			'comment'=>array(
				'heading'=>'备注',
				'eval'=>true,
				'cell'=>array('data'=>"return str_getSummary('{comment}',50);",'class'=>'ellipsis','title'=>'{comment}')
			)
		);
		
		$table=$this->table->setFields($field)
			->setRowAttributes(array('hash'=>'client/edit/{id}'))
			->setData($this->client->getList($method))
			->generate();
		$this->load->addViewData('list', $table);
		$this->load->view('list');
	}

	function add(){
		$this->client->id=$this->client->add();
		$this->output->status='redirect';
		$this->output->data='client/edit/'.$this->client->id;
	}
	
	function subList($item,$client_id=false){
		if($client_id){
			$client=$this->client->fetch($client_id);
		}

		//客户相关人
		if($item=='relative'){
			$field=array(
				'relative_name'=>array('heading'=>'名称','cell'=>'{relative_name}<button type="submit" id="{id}" name="submit[remove_relative]" class="hover">删除</button>'), 
				'relative_phone'=>array('heading'=>'电话', 'orderby'=>false), 
				'relative_email'=>array('heading'=>'电邮', 'wrap'=>array('mark'=>'a', 'href'=>'mailto:{relative_email}')), 
				'relation'=>array('heading'=>'关系', 'orderby'=>false)
			);
			
			$list=$this->table->setFields($field)
				->setRowAttributes(array('hash'=>'client/edit/{reltive}'))
				->setData($this->client->getRelatives($this->client->id))
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
				->setData($this->client->getProfiles($this->client->id))
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
				->setData($this->cases->getListByPeople($this->client->id))
				->generate();
		}
		
		if(!$client_id){//没有指定$client_id，是在edit方法内调用
			$this->load->addViewData($item.'_list', $list);
		}else{
			return array('selector'=>'.item[name="'.$item.'"]>.contentTable','content'=>$list,'type'=>'html','method'=>'replace','content_name'=>'content-table');
		}

	}
	
	function edit($id){
		$this->client->id=$id;
		
		$this->load->model('staff_model','staff');
		$this->load->model('cases_model','cases');

		try{
			$client=$this->client->fetch($this->client->id);
			$labels=$this->client->getLabels($this->client->id);

			//取得当前客户的"来源"数据
			$source=$this->client->fetchSource($client['source']);

			if(!$client['abbreviation'] && !$client['name']){
				$client['name']='未命名客户';
			}

			$this->output->setData($client['abbreviation']?$client['abbreviation']:$client['name'],'name');

			$available_options=$this->client->getHotlabelsOfTypes();
			$profile_name_options=$this->client->getProfileNames();

			$this->subList('relative');
			$this->subList('profile');
			$this->subList('case');

			if($client['staff']){
				$client['staff_name']=$this->staff->fetch($client['staff'],'name');
			}

			$this->load->addViewArrayData(compact('client','labels','available_options','profile_name_options','source'));

			if($this->input->post('character') && in_array($this->input->post('character'),array('个人','单位'))){
				post('client/character', $this->input->post('character'));
			}

			$this->load->view('client/edit');
			$this->load->view('client/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}
	}

	function submit($submit,$id,$button_id=NULL){
		$this->client->id=$id;
		
		$client=array_merge($this->client->fetch($id),$this->input->sessionPost('client'));

		try{
		
			if($submit=='cancel'){
				unset($_SESSION[CONTROLLER]['post'][$this->client->id]);
				$this->client->clearUserTrash();
			}

			elseif($submit=='client'){
				$this->load->model('staff_model','staff');
				
				$labels=$this->input->sessionPost('labels');

				if($client['character'] != '个人' && $client['abbreviation'] == ''){
					//单位简称必填
					$this->output->message('请填写单位简称','warning');
					throw new Exception;
				}
				
				if(!isset($labels['类型'])){
					$this->output->message('请选择客户类型','warning');
					throw new Exception;
				}
				
				$source=$this->input->sessionPost('source');

				post('client/source', $this->client->setSource($source['type'], isset($source['detail'])?$source['detail']:NULL));
				
				post('client/staff', $this->staff->check($client['staff_name']));
				
				if(!$client['type']){
					post('client/type','客户');
				}

				$this->client->update($this->client->id,post('client'));
				
				$this->client->updateLabels($this->client->id,$labels);
				
				unset($_SESSION[CONTROLLER]['post'][$this->client->id]);
			}

			elseif($submit=='relative'){
				
				$relative=(array)post('relative');
				
				if(!isset($relative['relation'])){
					$this->output->message('请选择相关人与客户关系','warning');
					throw new Exception;
				}
				
				if(!$relative['id']){
					$profiles=(array)post('relative_profiles');
					
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
					$relative['id']=$this->client->add($relative);
					$this->output->message('新客户 <a href="#client/edit/' . $relative['id'] . '">' . $relative['name'] . ' </a>已经添加');
				}else{
					$this->output->message('系统中已经存在 ' . $relative['name'] . '，已自动识别并添加');
				}

				$this->client->addRelationship($this->client->id,$relative['id'],$relative['relation']);

				$this->output->setData($this->subList('relative',$this->client->id));

			}

			elseif($submit=='remove_relative'){
				$this->client->removeRelationship($this->client->id,$button_id);
				$this->output->setData($this->subList('relative',$this->client->id));
			}

			elseif($submit=='profile'){
				$profile=$this->input->sessionPost('profile');
				
				if(!$profile['name']){
					$this->output->message('请选择资料项名称','warning');
					throw new Exception;
				}
				
				$this->client->addProfile($this->client->id,$profile['name'],$profile['content'],$profile['comment']);
				
				$this->output->setData($this->subList('profile',$this->client->id));
				
				unset($_SESSION['client']['post'][$this->client->id]['profile']);
			}

			elseif($submit=='remove_profile'){
				$this->client->removeProfile($this->client->id,$button_id);
				$this->output->setData($this->subList('profile',$this->client->id));
			}
			
			$this->output->status='success';
			
		}catch(Exception $e){
			$this->output->status='fail';
		}
	}

	/**
	 * ajax响应页面，根据请求的字符串返回匹配的客户id和名称
	 */
	function match(){
		

		$term=$this->input->post('term');
		
		$result=$this->client->match($term);

		$array=array();

		foreach ($result as $row){
			$array[]=array(
				'label'=>$row['name'],
				'value'=>$row['id']
			);
		}
		$this->output->data=$array;
	}
}
?>