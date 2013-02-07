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
				'title'=>'名称',
				'content'=>'{abbreviation}',
				'td'=>'class="ellipsis" title="{name}" href="client/edit/{id}"'
			),
			'phone'=>array('title'=>'电话', 'td'=>'class="ellipsis" title="{phone}"'),
			'address'=>array(
				'title'=>'地址', 'td_title'=>'width="240px"',
				'td'=>'class="ellipsis" title="{address}"'
			),
			'comment'=>array(
				'title'=>'备注',
				'td'=>'class="ellipsis" title="{comment}"',
				'eval'=>true,
				'content'=>"return str_getSummary('{comment}',50);"
			)
		);
		
		$table=$this->table->setFields($field)
			->wrapForm()
			->setData($this->client->getList($method))
			->generate();
		$this->load->addViewData('list', $table);
		$this->load->view('list');
	}

	function add(){
		$this->edit();
	}
	
	function subList($item,$client_id=false){
		if($client_id){
			$client=$this->client->getPostData($client_id);
		}

		//客户相关人
		if($item=='relative'){
			$field=array(
				'relative_name'=>array(
					'title'=>'<input type="submit" name="submit[relative_delete]" value="删" />名称<input type="submit" name="submit[relative_set_default]" value="默认" />', 
					'eval'=>true, 
					'content'=>"
						\$return='<input type=\"checkbox\" name=\"relative_check[]\" value=\"{id}\" >';
						\$return.='{relative_name}';
						if('{is_default_contact}'){
							\$return.='*';
						}
						return \$return;
					",
					'td'=>'href="client/edit/{relative}"',
					'orderby'=>false
				), 
				'relative_phone'=>array('title'=>'电话', 'orderby'=>false), 
				'relative_email'=>array('title'=>'电邮', 'wrap'=>array('mark'=>'a', 'href'=>'mailto:{relative_email}')), 
				'relation'=>array('title'=>'关系', 'orderby'=>false)
			);
			
			$list=$this->table->setFields($field)
				->setData($this->client->getRelatives($this->client->id))
				->wrapBox(false)
				->generate();

		}
		//资料项
		elseif($item=='profile'){
			$field=array(
				'name'=>array('title'=>'<input type="submit" name="submit[people_profile_delete]" value="删" />名称', 'content'=>'<input type="checkbox" name="people_profile_check[]" value="{id}" />{name}', 'orderby'=>false), 
				'content'=>array('title'=>'内容', 'eval'=>true, 'content'=>"
					if('{name}'=='电子邮件'){
						return '<a href=\"mailto:{content}\" target=\"_blank\">{content}</a>';
					}else{
						return '{content}';
					}
				", 'orderby'=>false), 
				'comment'=>array('title'=>'备注', 'orderby'=>false)
			);
			
			$list=$this->table->setFields($field)
				->setData($this->client->getProfiles($this->client->id))
				->wrapBox(false)
				->generate();

		}
		//相关案件
		elseif($item=='case'){
			$field=array(
				'num'=>array(
					'title'=>'案号',
					'td'=>'href="cases/edit/{id}"',
					'orderby'=>false
				),
				'case_name'=>array(
					'title'=>'案名', 
					'orderby'=>false
				), 
				'lawyers'=>array(
					'title'=>'主办律师', 
					'orderby'=>false
				)
			);
			$list=$this->table->setFields($field)
				->setData($this->cases->getListByPeople($this->client->id))
				->wrapBox(false)
				->generate();
		}
		
		if(!$client_id){//没有指定$client_id，是在edit方法内调用
			$this->load->addViewData($item.'_list', $list);
		}else{
			return array('selector'=>'.item[name="'.$item.'"]>.contentTable','content'=>$list,'type'=>'html','method'=>'replace');
		}

	}
	
	function edit($id=NULL){
		$this->load->model('staff_model','staff');
		$this->load->model('cases_model','cases');

		$client=$this->client->getPostData($id);
		$labels=$this->client->getLabels($this->client->id);
		//取得当前客户的"来源"数据
		$source=$this->client->fetchSource($client['source']);

		$this->output->setData($client['abbreviation'],'name');

		$available_options=$this->client->getHotlabelsOfTypes();
		$profile_name_options=$this->client->getProfileNames();
		
		$this->subList('relative');
		$this->subList('profile');
		$this->subList('case');

		$this->load->addViewArrayData(compact('client','labels','available_options','profile_name_options','source'));

		if($client['staff']){
			$client['staff_name']=$this->staff->fetch($client['staff'],'name');
		}

		if($this->input->post('character') && in_array($this->input->post('character'),array('自然人','单位'))){
			post('client/character', $this->input->post('character'));
		}

		if(post('client/character') == '单位'){
			$this->load->view('client/add_artificial');

		}else{
			$this->load->view('client/add_natural');
		}		
		
	}

	function submit($submit,$id){
		
		$client=array_merge($this->client->getPostData($id),(array)post('client'))+(array)$this->input->post('client');

		try{
		
			if($submit=='cancel'){
				unset($_SESSION[CONTROLLER]['post'][$this->client->id]);
				$this->client->clearUserTrash();
			}

			elseif($submit=='client'){
				$this->load->model('staff_model','staff');

				if($client['character'] == '自然人'){
					//自然人简称就是名称
					post('client/abbreviation', $client['name']);

				}elseif($client['abbreviation'] == ''){
					//单位简称必填
					$this->output->message('请填写单位简称','warning');
				}
				
				$source=(array)post('source')+$this->input->post('source');

				post('client/source', $this->client->setSource($source['type'], isset($source['detail'])?$source['detail']:NULL));
				
				post('client/staff', $this->staff->check($client['staff_name']));

				$this->client->update($this->client->id,post('client'));
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
						'character'=>isset($relative['character']) && $relative['character'] == '单位' ? '单位' : '自然人',
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

			elseif($submit=='relative_delete'){
				$this->client->removeRelationship($this->input->post('relative_check'));
				$this->output->setData($this->subList('relative',$this->client->id));
			}

			elseif($submit=='profile'){
				$profile=(array)post('profile')+$this->input->post('profile');
				
				if($profile['name']==''){
					$this->output->message('请选择资料项名称','warning');
					throw new Exception;
				}
				
				$this->client->addProfile($this->client->id,$profile['name'],$profile['content'],$profile['comment']);
				
				$this->output->setData($this->subList('profile',$this->client->id));
				
				unset($_SESSION['client']['post'][$this->client->id]['profile']);
			}

			elseif($submit=='people_profile_delete'){
				$this->client->removeProfile($this->input->post('people_profile_check'));
				$this->output->setData($this->subList('profile',$this->client->id));
			}
			
			elseif($submit=='relative_set_default'){
				if(count(post('relative_check')) > 1){
					$this->output->message('你可能试图设置多个默认联系人，这是不被允许的', 'warning');

				}elseif(count(post('relative_check') == 1)){
					$relative_set_default_keys=array_keys(post('relative_check'));
					$this->client->setDefaultRelated($relative_set_default_keys[0], post('client/id'));

					$this->output->message('成功设置默认联系人');

				}elseif(count(post('relative_check') == 0)){
					$this->client->clearDefaultRelated(post('client/id'));
				}
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