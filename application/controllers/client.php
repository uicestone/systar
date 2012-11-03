<?php
class Client extends SS_Controller{
	function __construct(){
		parent::__construct();
	}
	
	function potential(){
		$this->lists('potential');
	}
        
        function lists($method=NULL){
            if(is_posted('delete')){
                    $_POST=array_trim($_POST);
                    $this->client->delete($_POST['client_check']);
            }
            $field=array(
                    'abbreviation'=>array(
                        'title'=>'名称',
                        'content'=>'<input type="checkbox" name="client_check[{id}]" /><a href="javascript:showWindow(\'client/edit/{id}\')" title="{name}">{abbreviation}</a>',
                        'td'=>'class="ellipsis"'
                    ),
                    'phone'=>array(
                        'title'=>'电话',
                        'td'=>'class="ellipsis" title="{phone}"'
                    ),
                    'address'=>array(
                        'title'=>'地址',
                        'td_title'=>'width="240px"',
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
                    ->setMenu('<input type="submit" name="delete" value="删除" />','left')
                    ->setData($this->client->getList($method))
                    ->generate();
            $this->load->addViewData('list',$table);
            $this->load->view('list');            
        }
	
	function _lists($method=NULL){
		
		if(is_posted('delete')){
			$_POST=array_trim($_POST);
			$this->client->delete($_POST['client_check']);
		}
		
		$q="
			SELECT client.id,client.name,client.abbreviation,client.time,client.comment,
				phone.content AS phone,address.content AS address
			FROM `client` 
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
				)phone ON client.id=phone.client
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='地址' GROUP BY client
				)address ON client.id=address.client
			WHERE display=1 AND classification='客户'
		";
		
		$q_rows="
			SELECT COUNT(client.id)
			FROM `client` 
			WHERE display=1 AND classification='客户'
		";
		
		$condition='';
		if($method=='potential'){
			$condition.=" AND type='潜在客户'";
		
		}else{
			$condition.="
				AND type='成交客户'
				AND client.id IN (SELECT client FROM case_client WHERE `case` IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."'))
		";
		}
		
		$search_bar=$this->processSearch($condition,array('name'=>'姓名','work_for'=>'单位','address'=>'地址','comment'=>'备注'));
		
		$this->processOrderby($condition,'time','DESC',array('abbreviation','type','address','comment'));
		
		$q.=$condition;$q_rows.=$condition;
		
		$list_locator=$this->processMultiPage($q,$q_rows);
		
		$field=array(
			'abbreviation'=>array('title'=>'名称','content'=>'<input type="checkbox" name="client_check[{id}]" />
			<a href="javascript:showWindow(\'client/edit/{id}\')" title="{name}">{abbreviation}</a>',
				'td'=>'class="ellipsis"'
			),
			'phone'=>array('title'=>'电话','td'=>'class="ellipsis" title="{phone}"'),
			'address'=>array('title'=>'地址','td_title'=>'width="240px"',
				'td'=>'class="ellipsis" title="{address}"'
			),
			'comment'=>array('title'=>'备注','td'=>'class="ellipsis" title="{comment}"','eval'=>true,'content'=>"
				return str_getSummary('{comment}',50);
			",
			)
		);
		$menu=array(
			'head'=>'<div class="left">'.
						'<input type="submit" name="delete" value="删除" />'.
					'</div>'.
					'<div class="right">'.
						$list_locator.
					'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q,$field);
		
		$this->view_data+=compact('table','menu','search_bar');
		
		$this->load->view('lists',$this->view_data);
		$this->main_view_loaded=TRUE;
		
		$this->load->view('sidebar_head');
		$this->load->view('client/lists_sidebar');
		$this->load->view('sidebar_foot');
		$this->sidebar_loaded=TRUE;
		
	}

	function add(){
		$this->edit();
	}
	
	function edit($id=NULL){
		$this->load->model('staff_model');
		
		$this->getPostData($id,function($CFG){
			post('client/name',$_SESSION['username'].'的新客户 '.date('Y-m-d h:i:s',$CFG->item('timestamp')));
			post('client/abbreviation',$_SESSION['username'].'的新客户 '.date('Y-m-d h:i:s',$CFG->item('timestamp')));
			
			post('client_extra/source_lawyer_name',$_SESSION['username']);
		});
		
		$q_source="SELECT * FROM client_source WHERE id='".post('client/source')."'";
		$r_source=db_query($q_source);
		post('source',db_fetch_array($r_source));
		//取得当前客户的"来源"数据
		
		if(post('client/source_lawyer')){
			post('client_extra/source_lawyer_name',db_fetch_field("SELECT name FROM staff WHERE id ='".post('client/source_lawyer')."'"));
		}
		
		if(is_posted('character')){
			post('client/character',$_POST['character']);
		}
		
		$submitable=false;//可提交性，false则显示form，true则可以跳转
		
		if(is_posted('submit')){
			$submitable=true;
		
			$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$_POST);
		
			if(is_posted('submit/client_client')){
				post('client_client_extra/show_add_form',true);
				
				$client_check=client_check(post('client_client_extra/name'),'array');
		
				if($client_check>0){
					post('client_client/client_right',$client_check['id']);
					showMessage('系统中已经存在 '.$client_check['name'].'，已自动识别并添加');
		
				}elseif($client_check==-1){//如果client_client添加的客户不存在，则先添加客户
					$new_client=array(
						'name'=>post('client_client_extra/name'),
						'abbreviation'=>post('client_client_extra/name'),
						'character'=>post('client_client_extra/character')=='单位'?'单位':'自然人',
						'classification'=>'客户',
						'type'=>'潜在客户',
					);
					post('client_client/client_right',client_add($new_client));
					
					$this->model->addContact_phone_email(post('client_client/client_right'),post('client_client_extra/phone'),post('client_client_extra/email'));
		
					showMessage(
						'<a href="javascript:showWindow(\'client?edit='.$new_client['id'].'\')" target="_blank">新客户 '.
						$new_client['name'].
						' 已经添加，点击编辑详细信息</a>',
					'notice');
		
				}else{
					//除了不存在意外的其他错误，如关键字多个匹配
					$submitable=false;
				}
		
				post('client_client/client_left',post('client/id'));
				
				if($submitable && $this->model->addRelated(post('client_client'))){
					unset($_SESSION['client']['post']['client_client']);
					unset($_SESSION['client']['post']['client_client_extra']);
				}
			}
			
			if(is_posted('submit/client_contact')){
				post('client_contact/client',post('client/id'));
				
				if($this->model->addContact(post('client_contact'))){
					unset($_SESSION['client']['post']['client_contact']);
				}
			}
			
			if(is_posted('submit/client_client_set_default')){
				if(count(post('client_client_check'))>1){
					showMessage('你可能试图设置多个默认联系人，这是不被允许的','warning');
		
				}elseif(count(post('client_client_check')==1)){
					$client_client_set_default_keys=array_keys(post('client_client_check'));
					$this->model->setDefaultRelated($client_client_set_default_keys[0],post('client/id'));
		
					showMessage('成功设置默认联系人');
		
				}elseif(count(post('client_client_check')==0)){
					$this->model->clearDefaultRelated(post('client/id'));
				}
			}
		
			if(is_posted('submit/client_client_delete')){
				$this->model->deleteRelated(post('client_client_check'));
			}
		
			if(is_posted('submit/client_contact_delete')){
				$this->model->deleteContact(post('client_contact_check'));
			}
			
			if(post('client/character')=='自然人'){
				//自然人简称就是名称
				post('client/abbreviation',post('client/name'));
				if(!post('client/birthday')){
					unset($_SESSION['client']['post']['client']['birthday']);
				}
		
			}elseif(array_dir('_POST/client/abbreviation')==''){
				//单位简称必填
				$submitable=false;
				showMessage('请填写客户简称','warning');
			}
			
			if(!post('client/source',$this->model->setSource(post('source/type'),post('source/detail')))){
				$submitable=false;
			}
			
			if(post('client/source_lawyer',$this->staff_model->check(post('client_extra/source_lawyer_name'),'id',true,'client/source_lawyer'))<0){
				$submitable=false;
			}
			$this->processSubmit($submitable);
		}
		
		//准备client_add表单中的小表
		$q_client_client="
			SELECT 
				client_client.id AS id,client_client.role,client_client.client_right,client_client.is_default_contact,
				client.abbreviation AS client_right_name,client.classification,
				phone.content AS client_right_phone,email.content AS client_right_email
			FROM 
				client_client INNER JOIN client ON client_client.client_right=client.id
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
				)phone ON client.id=phone.client
				LEFT JOIN (
					SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='电子邮件' GROUP BY client
				)email ON client.id=email.client
			WHERE `client_left`='".post('client/id')."'
			ORDER BY role";
		
		$field_client=array(
			'checkbox'=>array('title'=>'<input type="submit" name="submit[client_client_delete]" value="删" />','orderby'=>false,'content'=>'<input type="checkbox" name="client_client_check[{id}]" >','td_title'=>' width=60px'),
			'client_right_name'=>array('title'=>'名称<input type="submit" name="submit[client_client_set_default]" value="默认" />','eval'=>true,'content'=>"
				\$return='';
				\$return.='<a href=\"javascript:showWindow(\''.('{classification}'=='客户'?'client':'contact').'?edit={client_right}\')\">{client_right_name}</a>';
				if('{is_default_contact}'){
					\$return.='*';
				}
				return \$return;
			",'orderby'=>false),
			'client_right_phone'=>array('title'=>'电话','orderby'=>false),
			'client_right_email'=>array('title'=>'电邮','surround'=>array('mark'=>'a','href'=>'mailto:{client_right_email}')),
			'role'=>array('title'=>'关系','orderby'=>false)
		);
		
		$q_client_contact="
			SELECT 
				client_contact.id,client_contact.comment,client_contact.content,client_contact.type
			FROM client_contact INNER JOIN client ON client_contact.client=client.id
			WHERE client_contact.client='".post('client/id')."'
		";
		
		$field_client_contact=array(
			'checkbox'=>array('title'=>'<input type="submit" name="submit[client_contact_delete]" value="删" />','orderby'=>false,'content'=>'<input type="checkbox" name="client_contact_check[{id}]" >','td_title'=>' width=60px'),
			'type'=>array('title'=>'类别','orderby'=>false),
			'content'=>array('title'=>'内容','eval'=>true,'content'=>"
				if('{type}'=='电子邮件'){
					return '<a href=\"mailto:{content}\" target=\"_blank\">{content}</a>';
				}else{
					return '{content}';
				}
			",'orderby'=>false),
			'comment'=>array('title'=>'备注','orderby'=>false)
		);
		
		$q_client_case="
		SELECT case.id,case.name AS case_name,case.num,	
			GROUP_CONCAT(DISTINCT staff.name) AS lawyers
		FROM `case`
			LEFT JOIN case_lawyer ON (case.id=case_lawyer.case AND case_lawyer.role='主办律师')
			LEFT JOIN staff ON staff.id=case_lawyer.lawyer
		WHERE case.id IN (
			SELECT `case` FROM case_client WHERE client='".post('client/id')."'
		)
		GROUP BY case.id
		HAVING id IS NOT NULL
		";
		
		$field_client_case=array(
			'num'=>array('title'=>'案号','surround'=>array('mark'=>'a','href'=>'javascript:window.rootOpener.location.href=\'case?edit={id}\';window.opener.parent.focus();'),'orderby'=>false),
			'case_name'=>array('title'=>'案名','orderby'=>false),
			'lawyers'=>array('title'=>'主办律师','orderby'=>false)
		);
		
		$data=compact('q_client_client','field_client','q_client_contact','field_client_contact','q_client_case','field_client_case');
		
		$this->load->view('head',$data);
		
		if(post('client/character')=='单位'){
			$this->load->view('client/add_artificial');
		
		}else{
			$this->load->view('client/add_natural');
		}
		
	}
	
	function autocomplete(){$type=NULL;
		got('type') && $type=$_GET['type'];
		
		$result=$this->client->match($_POST['term'],'client',$type);
		
		$array=array();
		
		foreach($result as $line_id => $content_array){
			$array[$line_id]['label']=$content_array['name'];
			$array[$line_id]['value']=$content_array['id'];
		}
		echo json_encode($array);
	}
	
	function getSourceLawyer(){
		model('staff');
		$staff=$this->staff->fetch(client_check($_POST['client_name'],'source_lawyer'));
		if($staff){
			echo $staff['name'];
		}
	}
}
?>