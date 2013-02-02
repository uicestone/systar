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

		if($this->input->post('delete')){
			$clients_to_delete=array_trim($this->input->post('client_check'));
			$this->client->delete($clients_to_delete);
		}

		$field=array(
			'abbreviation'=>array(
				'title'=>'名称',
				'content'=>'<input type="checkbox" name="client_check[{id}]" />
					<a href="javascript:showWindow(\'client/edit/{id}\')" title="{name}">{abbreviation}</a>',
				'td'=>'class="ellipsis"'
			),
			'phone'=>array('title'=>'电话', 'td'=>'class="ellipsis" title="{phone}"'),
			'address'=>array('title'=>'地址', 'td_title'=>'width="240px"',
			'td'=>'class="ellipsis" title="{address}"'),
			'comment'=>array(
				'title'=>'备注',
				'td'=>'class="ellipsis" title="{comment}"',
				'eval'=>true,
				'content'=>"return str_getSummary('{comment}',50);"
			)
		);
		
		$table=$this->table->setFields($field)
			->setMenu('<input type="submit" name="delete" value="删除" />', 'left')
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
				'client_right_name'=>array(
					'title'=>'<input type="submit" name="submit[client_client_delete]" value="删" />名称<input type="submit" name="submit[client_client_set_default]" value="默认" />', 
					'eval'=>true, 
					'content'=>"
						\$return='<input type=\"checkbox\" name=\"client_client_check[{id}]\" >';
						\$return.='<a href=\"javascript:showWindow(\''.('{classification}'=='客户'?'client':'contact').'/edit/{client_right}\')\">{client_right_name}</a>';
						if('{is_default_contact}'){
							\$return.='*';
						}
						return \$return;
					", 'orderby'=>false
				), 
				'client_right_phone'=>array('title'=>'电话', 'orderby'=>false), 
				'client_right_email'=>array('title'=>'电邮', 'wrap'=>array('mark'=>'a', 'href'=>'mailto:{client_right_email}')), 
				'role'=>array('title'=>'关系', 'orderby'=>false)
			);
			
			$list=$this->table->setFields($field)
				->setData($this->client->getRelatedClients($this->client->id))
				->wrapBox(false)
				->generate();

		}
		//资料项
		elseif($item=='profile'){
			$field=array(
				'type'=>array('title'=>'<input type="submit" name="submit[client_contact_delete]" value="删" />类别', 'content'=>'<input type="checkbox" name="client_contact_check[{id}]" />{type}', 'orderby'=>false), 
				'content'=>array('title'=>'内容', 'eval'=>true, 'content'=>"
					if('{type}'=='电子邮件'){
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
			$field=array('num'=>array('title'=>'案号', 'wrap'=>array('mark'=>'a', 'href'=>'javascript:window.rootOpener.location.href=\'/cases/edit/{id}\';window.opener.parent.focus();'), 'orderby'=>false), 'case_name'=>array('title'=>'案名', 'orderby'=>false), 'lawyers'=>array('title'=>'主办律师', 'orderby'=>false));
			$list=$this->table->setFields($field)
				->setData($this->cases->getListByClient($this->client->id))
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
		
		$this->output->setData('html',$client['name'],'name');
		
		$this->subList('relative');
		$this->subList('profile');
		$this->subList('case');

		//取得当前客户的"来源"数据
		$source=$this->client->fetchSource($client['source']);

		$this->load->addViewArrayData(compact('client','source'));

		if($client['staff']){
			$client['staff_name']=$this->staff->fetch($client['staff'],'name');
		}

		if($this->input->post('character') && in_array($this->input->post('character'),array('自然人','单位'))){
			post('client/character', $this->input->post('character'));
		}

		$data=compact('client_table','contact_table','case_table');
		$this->load->addViewArrayData($data);

		if(post('client/character') == '单位'){
			$this->load->view('client/add_artificial');

		}else{
			$this->load->view('client/add_natural');
		}		
		
	}

	function submit($submit){

		$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'], $_POST);

		if($submit=='client_client'){
			post('client_client_extra/show_add_form', true);

			$client_check=$this->client->check(post('client_client_extra/name'), 'array');

			if($client_check > 0){
				post('client_client/client_right', $client_check['id']);
				showMessage('系统中已经存在 ' . $client_check['name'] . '，已自动识别并添加');

			}elseif($client_check == -1){//如果client_client添加的客户不存在，则先添加客户
				$new_client=array('name'=>post('client_client_extra/name'), 'abbreviation'=>post('client_client_extra/name'), 'character'=>post('client_client_extra/character') == '单位' ? '单位' : '自然人', 'classification'=>'客户', 'type'=>'潜在客户', );
				post('client_client/client_right', $this->client->add($new_client));

				$this->client->addContact_phone_email(post('client_client/client_right'), post('client_client_extra/phone'), post('client_client_extra/email'));

				showMessage('<a href="javascript:showWindow(\'client/edit/' . $new_client['id'] . '\')" target="_blank">新客户 ' . $new_client['name'] . ' 已经添加，点击编辑详细信息</a>', 'notice');

			}else{
				//除了不存在意外的其他错误，如关键字多个匹配
				$submitable=false;
			}

			post('client_client/client_left', post('client/id'));

			if($submitable && $this->client->addRelated(post('client_client'))){
				unset($_SESSION['client']['post']['client_client']);
				unset($_SESSION['client']['post']['client_client_extra']);
			}
		}

		if($submit=='client_contact'){
			post('client_contact/client', post('client/id'));

			if($this->client->addContact(post('client_contact'))){
				unset($_SESSION['client']['post']['client_contact']);
			}
		}

		if($submit=='client_client_set_default'){
			if(count(post('client_client_check')) > 1){
				showMessage('你可能试图设置多个默认联系人，这是不被允许的', 'warning');

			}elseif(count(post('client_client_check') == 1)){
				$client_client_set_default_keys=array_keys(post('client_client_check'));
				$this->client->setDefaultRelated($client_client_set_default_keys[0], post('client/id'));

				showMessage('成功设置默认联系人');

			}elseif(count(post('client_client_check') == 0)){
				$this->client->clearDefaultRelated(post('client/id'));
			}
		}

		if($submit=='client_client_delete'){
			$this->client->deleteRelated(post('client_client_check'));
		}

		if($submit=='client_contact_delete'){
			$this->client->deleteContact(post('client_contact_check'));
		}

		if(post('client/character') == '自然人'){
			//自然人简称就是名称
			post('client/abbreviation', post('client/name'));
			if(!post('client/birthday')){
				unset($_SESSION['client']['post']['client']['birthday']);
			}

		}elseif(array_dir('_POST/client/abbreviation') == ''){
			//单位简称必填
			$submitable=false;
			showMessage('请填写客户简称', 'warning');
		}

		if(!post('client/source', $this->client->setSource(post('source/type'), post('source/detail')))){
			$submitable=false;
		}

		if(post('client/source_lawyer', $this->staff->check(post('client_extra/source_lawyer_name'), 'id', true, 'client/source_lawyer')) < 0){
			$submitable=false;
		}
		$this->processSubmit($submitable);
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
	
	/**
	 * ajax响应页面
	 * 接受客户名称$_POST['client_name']
	 * 打印出此客户的来源律师名称
	 */
	function getSourceLawyer(){
		$this->load->model('staff_model','staff');
		$client_name=$this->input->post('client_name');
		$client_id=$this->client->check($client_name,'id');
		$source_lawyer=$this->client->fetch($client_id, 'source_lawyer');
		$source_lawyer_name=$this->staff->fetch($source_lawyer);
	}
}
?>