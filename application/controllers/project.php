<?php
class Project extends SS_controller{
	
	var $section_title='项目';
	
	var $form_validation_rules=array();
	
	var $list_args;
	
	function __construct(){
		parent::__construct();
		
		$controller=CONTROLLER;
		
		$this->list_args=array(
			'name'=>array('heading'=>'案名','cell'=>'{name}'),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->$controller,'getCompiledLabels'),'args'=>array('{id}')))
			/*
			 * 此处被迫使用了$this->$controller来调用被继承后的model。
			 * 因为Project::__construct()时，Cases::__construct()尚未运行，
			 * $this->project=$this->cases也尚未运行，因此$this->project未定义
			 */
		);
	}
	
	function index(){
		
		
		//监测有效的名称选项
		if($this->input->post('name')!==false){
			option('search/name',$this->input->post('name'));
		}
		
		if($this->input->post('name')===''){
			option('search/name',NULL);
		}
		
		if(is_array($this->input->post('labels'))){
			
			if(is_null(option('search/labels'))){
				option('search/labels',array());
			}
			
			option('search/labels',$this->input->post('labels')+option('search/labels'));
		}
		
		//点击了取消搜索按钮，则清空session中的搜索项
		if($this->input->post('submit')=='search_cancel'){
			option('search/labels',array());
			option('search/name',NULL);
		}
		
		//提交了搜索项，但搜索项中没有labels项，我们将session中搜索项的labels项清空
		if($this->input->post('submit')==='search' && $this->input->post('labels')===false){
			option('search/labels',array());
		}
		
		$table=$this->table->setFields($this->list_args)
			->setRowAttributes(array('hash'=>CONTROLLER.'/edit/{id}'))
			->setData($this->project->getList(option('search')))
			->generate();

		$this->load->addViewData('list',$table);
		$this->load->view('list');
		$this->load->view('project/list_sidebar',true,'sidebar');
	}
	
	function add(){
		$this->project->id=$this->project->add();
		$this->edit($this->project->id);
		redirect('#'.CONTROLLER.'/edit/'.$this->people->id);
	}
	
	/**
	 * 获得一种项目的编辑页子表，如案件的相关客户列表
	 * 生成的html表格提供“载入到视图”（适用于edit方法内载入）和“返回”（适用于ajax局部刷新时使用）两种方式
	 * @param type $item 子项目名，如client,document,schedule
	 * @param type $project_id 指定案号，默认false，即在edit方法内调用，因为已知$this->project->id，所以不用指定
	 * @param type $para 需要传递给subList中的列表程序的参数名和参数值。一般情况下只有在ajax局部刷新时才需要设定
	 * @return当$project_id==false（默认）时，无返回值，html表格作为字符串变量加载到视图
	 *	当$project_id为整数时，html表格作为字符串返回
	 */
	function subList($item,$project_id=false,$para=array()){
		if($project_id){
			$project=$this->project->fetch($project_id);
		}
		
		if($item=='client'){

			$fields=array(
				'name'=>array('heading'=>'名称','cell'=>array('data'=>'{name}<button type="submit" name="submit[remove_people]" id="{id}" class="hover">删除</button>')),
				'phone'=>array('heading'=>'电话','cell'=>array('class'=>'ellipsis','title'=>'{phone}')),
				'email'=>array('heading'=>'电邮','cell'=>array('data'=>'<a href = "mailto:{email}">{email}</a>','class'=>'ellipsis')),
				'role'=>array('heading'=>'本案地位'),
				'type'=>array('heading'=>array('data'=>'类型','width'=>'60px'))
			);
			$list=$this->table->setFields($fields)
				->setRowAttributes(array('hash'=>'client/edit/{people}'))
				->setAttribute('name',$item)
				->generate($this->project->getClientList($this->project->id));
		}
		elseif($item=='staff'){
			$fields=array(
				'staff_name'=>array('heading'=>'名称','cell'=>'{staff_name}<button type="submit" name="submit[remove_staff]" id="{id}" class="hover">删除</button>'),
				'role'=>array('heading'=>'本案职位'),
				'contribute'=>array('heading'=>'贡献','eval'=>true,'cell'=>"
					\$hours_sum_string='';
					if('{hours_sum}'){
						\$hours_sum_string='<span class=\"right\">{hours_sum}小时</span>';
					}

					return \$hours_sum_string.'<span>{contribute}'.('{contribute_amount}'?' ({contribute_amount})':'').'</span>';
				")
			);
			$list=$this->table->setFields($fields)
				->setAttribute('name',$item)
				->generate($this->project->getStaffList($this->project->id));

		}
		elseif($item=='fee'){
			$fields=array(
				'type'=>array('heading'=>'类型','cell'=>'{type}<button type="submit" name="submit[remove_fee]" id="{id}" class="hover">删除</button>'),
				'fee'=>array('heading'=>'数额','eval'=>true,'cell'=>"
					\$return='{fee}'.('{fee_received}'==''?'':' <span title=\"{fee_received_time}\">（到账：{fee_received}）</span>');
					if('{reviewed}'){
						\$return=wrap(\$return,array('mark'=>'span','style'=>'color:#AAA'));
					}
					return \$return;
				"),
				'condition'=>array('heading'=>'条件','cell'=>array('class'=>'ellipsis','title'=>'{condition}')),
				'pay_date'=>array('heading'=>'预计时间')
			);
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->project->getFeeList($this->project->id));
		}
		elseif($item=='miscfee'){
			$fields=array(
				'receiver'=>array('heading'=>'收款方','cell'=>'{receiver}<button type="submit" name="submit[remove_miscfee]" id="{id}" class="hover">删除</button>'),
				'fee'=>array('heading'=>'数额','eval'=>true,'cell'=>"
					return '{fee}'.('{fee_received}'==''?'':' （到账：{fee_received}）');
				"),
				'comment'=>array('heading'=>'备注'),
				'pay_date'=>array('heading'=>'预计时间')
			);
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->project->getFeeMiscList($this->project->id));
		}
		elseif($item=='schedule'){
			$fields=array(
				'name'=>array('heading'=>array('data'=>'标题','width'=>'150px'),'wrap'=>array('mark'=>'span','class'=>'show-schedule','id'=>'{id}')),
				'time_start'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'eval'=>true,'cell'=>"
					return date('m-d H:i',{time_start});
				"),
				'username'=>array('heading'=>array('data'=>'填写人','width'=>'90px'))
			);
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->schedule->getList(array('limit'=>10,'project'=>$this->project->id,'completed'=>true)));
		}
		elseif($item=='plan'){
			$fields=array(
				'name'=>array('heading'=>array('data'=>'标题','width'=>'150px'),'wrap'=>array('mark'=>'span','class'=>'show-schedule','id'=>'{id}')),
				'time_start'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'eval'=>true,'cell'=>"
					return date('m-d H:i',{time_start});
				"),
				'username'=>array('heading'=>array('data'=>'填写人','width'=>'90px'))
			);
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->schedule->getList(array('limit'=>10,'project'=>$this->project->id,'completed'=>false)));
		}
		elseif($item=='document'){
			$this->load->model('document_model','document');

			$fields=array(
				'extname'=>array(
					'heading'=>array('data'=>'','width'=>'40px'),
					'eval'=>true,
					'cell'=>"
						if('{extname}'==''){
							\$image='folder';
						}elseif(is_file('web/images/file_type/{extname}.png')){
							\$image='{extname}.png';
						}else{
							\$image='unknown';
						}
						return '<img src=\"/images/file_type/'.\$image.'.png\" alt=\"{extname}\" /><button type=\"submit\" name=\"submit[remove_document]\" id=\"{id}\" class=\"hover\">删除</button>';
					"
				),
				'name'=>array('heading'=>array('data'=>'文件名','width'=>'150px'),'cell'=>'<a href="/document/download/{document}">{name}</a>'),
				'type'=>array('heading'=>array('data'=>'类型','width'=>'80px')),
				'comment'=>array('heading'=>'备注'),
				'time'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'eval'=>true,'cell'=>"
					return date('m-d H:i',{time});
				"),
				'username'=>array('heading'=>array('data'=>'上传人','width'=>'90px'))
			);
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->project->getDocumentList($this->project->id));
		}
		
		if(!$project_id){//没有指定$project_id，是在edit方法内调用
			$this->load->addViewData($item.'_list', $list);
		}else{
			return array('selector'=>'.item[name="'.$item.'"]>.contentTable','content'=>$list,'type'=>'html','method'=>'replace','content_name'=>'content-table');
		}
	}

	function edit($id){
		$this->project->id=$id;
		
		$this->load->model('staff_model','staff');
		$this->load->model('client_model','client');
		$this->load->model('schedule_model','schedule');

		try{
			$project=array_merge($this->project->fetch($id),$this->input->sessionPost('project'));

			$labels=array_merge($this->project->getLabels($this->project->id),$this->input->sessionPost('labels'));

			if(!$project['name']){
				$this->section_title='未命名案件';
			}else{
				$this->section_title=$project['name'];
			}

			$project_role=$this->project->getRoles($this->project->id);

			$responsible_partner=$this->project->getPartner($project_role);
			//获得本案督办人的id

			$lawyers=$this->project->getLawyers($project_role);
			//获得本案办案人员的id

			$my_roles=$this->project->getMyRoles($project_role);
			//本人的本案职位

			$this->load->addViewArrayData(compact('project','labels','case_role','responsible_partner','lawyers','my_roles'));

			//计算本案有效日志总时间
			$this->load->view_data['schedule_time']=$this->schedule->calculateTime($this->project->id);

			$this->load->view_data['case_type_array']=array('诉前','一审','二审','再审','执行','劳动仲裁','商事仲裁');

			if(in_array('咨询',$labels)){
				$this->load->view_data['staff_role_array']=array('督办人','接洽律师','律师助理');
			}else{
				$this->load->view_data['staff_role_array']=array('案源人','督办人','接洽律师','主办律师','协办律师','律师助理');
			}

			if($project['timing_fee']){
				$this->load->view_data['case_fee_timing_string']=$this->project->getTimingFeeString($this->project->id);
			}

			$this->subList('client',false);

			$this->subList('staff',false);

			$this->subList('fee',false);

			$this->subList('miscfee');

			$this->subList('schedule');

			$this->subList('plan');

			$this->subList('document',false);

			$this->load->view('project/edit');
			
			$this->load->view('project/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}

	}

	function submit($submit,$id,$button_id=NULL){
		
		$this->project->id=$id;
		
		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
		//$project是用来汇总，读的，因此尽可能获取最新的，最多的信息。post('project')是要写入数据库的，只是要更改的部分。
		$project=array_merge($this->project->fetch($id),$this->input->sessionPost('project'));
		$labels=array_merge($this->project->getLabels($this->project->id),$this->input->sessionPost('labels'));
		
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
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]);
				$this->output->status='close';
			}
		
			elseif($submit=='project'){

				if(!$project['num']){
					$this->output->message('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
					throw new Exception();
				}
				if(isset($labels['分类']) && in_array($labels['分类'],array('诉讼','非诉讼')) && !in_array('咨询', $labels) && !$project['focus']){
					$this->output->message('请填写案件争议焦点','warning');
					throw new Exception;
				}
				
				$this->project->update($this->project->id,post('project'));
				$this->project->updateLabels($this->project->id,post('labels'));
				
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]);
				$this->output->status='close';
			}
			
			elseif($submit=='case_client'){
				
				//这样对数组做加法，后者同名键不会替换前者，即后者是前者的补充，而非更新
				$project_client=$this->input->sessionPost('case_client');
				$client=$this->input->sessionPost('client');
				$client_profiles=$this->input->sessionPost('client_profiles');
				$client_labels=$this->input->sessionPost('client_labels');
				
				if(!$project_client['role']){
					$this->output->message('请选择本案地位','warning');
					throw new Exception;
				}
		
				if($project_client['client']){//autocomplete搜索到已有客户
					$this->output->message("系统中已经存在{$client['name']}，已自动识别");
				}
				else{//添加新客户
					if(!$client['name']){
						$this->output->message('请输入客户或相关人名称', 'warning');
						throw new Exception;
					}
					$new_client=array(
						'name'=>$client['name'],
						'character'=>isset($client['character']) && $client['character']=='单位'?'单位':'个人',
						'type'=>$client['type'],
						'labels'=>$client_labels
					);

					if(!$client_profiles['电话'] && !$client_profiles['电子邮件']){
						$this->output->message('至少输入一种联系方式', 'warning');
						throw new Exception;
					}
					
					foreach($client_profiles as $name => $content){
						if($name=='电话'){
							if($this->client->isMobileNumber($content)){
								$new_client['profiles']['手机']=$content;
							}else{
								$new_client['profiles']['电话']=$content;
							}
						}elseif($name=='电子邮件' && $content){
							if(!$this->form_validation->valid_email($content)){
								$this->output->message('请填写正确的Email地址', 'warning');
								throw new Exception;
							}
						}else{
							$new_client['profiles'][$name]=$content;
						}
					}

					if($client['type']=='客户'){//客户必须输入来源
						if(!$client_profiles['来源类型']){
							$this->output->message('请选择客户来源类型','warning');
							throw new Exception;
						}
						$client['staff']=$this->staff->check($client['staff_name']);

						$new_client['staff']=$client['staff'];

					}else{//非客户必须输入工作单位
						if(!$client['work_for']){
							$this->output->message('请输入工作单位','warning');
							throw new Exception;
						}
					}
					
					if($client['work_for']){
						$new_client['work_for']=$client['work_for'];
					}
					
					$project_client['client']=$this->client->add($new_client);

					$this->output->message(
						'<a href="#'.
						($client['type']=='客户'?'client':'contact').
						'/edit/'.$project_client['client'].'">新'.
						$client['type'].' '.$client['name'].
						' 已经添加，点击编辑详细信息</a>'
					);
				}

				if($this->project->addPeople($this->project->id,$project_client['client'],'客户',$project_client['role'])){
					$this->output->setData($this->subList('client',$this->project->id));
				}else{
					$this->output->message('客户添加错误', 'warning');
					throw new Exception;
				}
				
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['case_client']);
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['client']);
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['client_profiles']);
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['client_labels']);
			}

			elseif($submit=='remove_people'){
				if($this->project->removePeople($this->project->id,$button_id)){
					$this->output->setData($this->subList('client',$this->project->id));
				}
			}
			
			elseif($submit=='staff'){
				
				$staff=$this->input->sessionPost('staff');
				if(!$staff['id']){
					$staff['id']=$this->staff->check($staff['name']);
					
					if($staff['id']){
						post('staff/id',$staff['id']);
					}else{
						$this->output->message('请输入职员名称','warning');
						throw new Exception;
					}
				}
				
				$project_role=$this->project->getRoles($this->project->id);
		
				$responsible_partner=$this->project->getPartner($project_role);
				//获得本案督办人的id
				$my_roles=$this->project->getMyRoles($project_role);
				//本人的本案职位

				if($staff['role']=='实际贡献' && !(in_array('督办人',$my_roles) || in_array('主办律师',$my_roles))){
					//禁止非主办律师/合伙人分配实际贡献
					$this->output->message('你没有权限分配实际贡献');
					throw new Exception();
				}

				if(!$responsible_partner && $staff['role']!='督办人'){
					//第一次插入督办人后不显示警告，否则如果不存在督办人则显示警告
					$this->output->message('未设置督办人','notice');
				}

				if(!$staff['role']){
					$this->output->message('未选择本案职务','warning');
					throw new Exception();
				}

				if($this->project->addStaff($this->project->id,post('staff/id'),post('staff/role'),post('staff/hourly_fee'))){
					$this->output->setData($this->subList('staff',$this->project->id));
					unset($_SESSION[CONTROLLER]['post'][$this->project->id]['staff']['id']);
				}else{
					$this->output->message('人员添加错误', 'warning');
				}

				//$this->project->calcContribute($this->project->id);
				
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['staff']);
			}
			
			elseif($submit=='remove_staff'){
				if($this->project->removePeople($this->project->id,$button_id)){
					$this->output->setData($this->subList('staff',$this->project->id));
				}
			}
			
			elseif($submit=='case_fee'){
				
				$project_fee=$this->input->sessionPost('case_fee');

				if(!$project_fee['type']){
					$this->output->message('请选择收费类型','warning');
				}
				
				if(!is_numeric($project_fee['fee'])){
					$this->output->message('请预估收费金额（数值）','warning');
				}
				
				if(!$project_fee['pay_date']){
					$this->output->message('请预估收费时间','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception;
				}
				
				if($this->project->addFee($this->project->id,$project_fee['fee'],$project_fee['pay_date'],$project_fee['type'],$project_fee['condition'])){
					//unset($_SESSION['cases']['post']['case_fee']);
					$this->output->setData($this->subList('fee',$this->project->id));
				}else{
					$this->output->message('收费添加错误', 'warning');
				}
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['case_fee']);
			}
			
			elseif($submit=='remove_fee' || $submit=='remove_miscfee'){
				$this->project->removeFee($this->project->id,$button_id);
				
				if($submit=='remove_fee'){
					$this->output->setData($this->subList('fee',$this->project->id));
				}else{
					$this->output->setData($this->subList('miscfee',$this->project->id));
				}
			}
			
			elseif($submit=='case_fee_review' && $this->user->isLogged('finance')){
				//财务审核
				$this->project->ignoreFee($fee_ids);
			}
			
			elseif($submit=='case_fee_timing'){
				
				$timing_fee=$this->input->sessionPost('case_fee_timing');

				if(!$timing_fee){
					$this->project->removeTimingFee($this->project->id);
				}

				if(
					post(CONTROLLER.'/timing_fee') && 
					(!$timing_fee['date_start'] || $timing_fee['included_hours']==='')
				){
					$this->output->message('账单起始日或包含小时数未填','warning');
					throw new Exception();

				}else{
					if($this->project->setTimingFee($this->project->id,$timing_fee['date_start'],$timing_fee['bill_day'],$timing_fee['payment_day'],$timing_fee['included_hours'],$timing_fee['payment_cycle'],$timing_fee['contract_cycle'])){
						unset($_SESSION[CONTROLLER]['post'][$this->project->id]['case_fee_timing']);
					}
				}
				
				$this->output->status='refresh';
			}
			
			elseif($submit=='case_fee_misc'){
				
				$misc_fee=$this->input->sessionPost('case_fee_misc');
				
				if(!$misc_fee['receiver']){
					$this->output->message('请选择办案费收款方','warning');
				}
				
				if(!$misc_fee['fee']){
					$this->output->message('请填写办案费约定金额（数值）','warning');
				}
				
				if(!$misc_fee['pay_date']){
					$this->output->message('请填写收费时间','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception();
				}
				
				if($this->project->addFee($this->project->id,$misc_fee['fee'],$misc_fee['pay_date'],'办案费',NULL,$misc_fee['receiver'],$misc_fee['comment'])){
					$this->output->setData($this->subList('miscfee',$this->project->id));
				}else{
					$this->output->message('收费添加错误', 'warning');
				}
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['case_fee_misc']);
			}
			
			elseif($submit=='case_document'){
				$this->load->model('document_model','document');
				
				$document=$this->input->sessionPost('document');
				
				$document_labels=$this->input->sessionPost('document_labels');
				
				if(!$document_labels['类型']){
					$this->output->message('请选择文件类型','warning');
					throw new Exception;
				}
				
				$config['upload_path'] = $this->config->item('document_path');
				$config['encrypt_name'] = true;
				$config['allowed_types'] = $this->config->item('允许上传的文件类型');

				$this->load->library('upload', $config);

				if (!$this->upload->do_upload('document')){
					$this->output->message($this->upload->display_errors(),'warning');
					throw new Exception;
				}
				
				$upload_info=$this->upload->data();
				
				if(!$document_labels['类型']){
					$this->output->message('请选择文件类型','warning');
					throw new Exception;
				}
				
				$document['name']=$upload_info['client_name'];
				$document['size']=$upload_info['file_size'];
				$document['extname']=$upload_info['file_ext'];
				$document['type']=$upload_info['file_type'];
				
				$document['id']=$this->document->add($document);
				
				$this->document->updateLabels($document['id'],$document_labels);
				
				$this->project->addDocument($this->project->id, $document['id']);
				
				rename($this->config->item('document_path').$upload_info['file_name'], $this->config->item('document_path').$document['id']);
				
				$this->output->setData($this->subList('document', $this->project->id));
				
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['document']);
			}
			
			elseif($submit=='remove_document'){
				if($this->project->removeDocument($this->project->id,$button_id)){
					$this->output->setData($this->subList('document',$this->project->id));
				}
			}
			
			elseif($submit=='file_document_list'){
				
				$this->load->model('document_model','document');

				$document_catalog=$this->project->getDocumentCatalog($this->project->id,post('case_document_check'));

				$this->load->view('case/document_catalog');
			}
			
			elseif($submit=='new_case'){
				$this->project->removeLabel($this->project->id, '已归档');
				$this->project->removeLabel($this->project->id, '咨询');
				$this->project->addLabel($this->project->id, '等待立案审核');
				$this->project->addLabel($this->project->id, '案件');
				$this->project->update($this->project->id,array(
					'num'=>NULL,
					'time_contract'=>$this->date->today,
					'time_end'=>date('Y-m-d',$this->date->now+100*86400)
				));
				
				$this->output->message('已立案，请立即获得案号');
				
				$this->output->status='refresh';
			}
			
			elseif($submit=='file' && in_array('咨询',$labels)){
				$this->project->addLabel($this->project->id, '已归档');
				$this->output->status='refresh';
				$this->output->message('咨询案件已归档');
			}
			
			elseif($submit=='review'){
				$this->project->removeLabel($this->project->id, '等待立案审核');
				$this->project->addLabel($this->project->id, '在办');
				$this->output->status='refresh';
				$this->output->message('通过立案审核');
			}
			
			elseif($submit=='apply_lock'){
				//@TODO申请锁定，通过标签和消息系统来解决
			}
			
			elseif($submit=='lock_type'){
				$this->project->addLabel($this->project->id, '类型已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_client'){
				$this->project->addLabel($this->project->id, '客户已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_staff'){
				$this->project->addLabel($this->project->id, '职员已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_fee'){
				$this->project->addLabel($this->project->id, '费用已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_client'){
				$this->project->removeLabel($this->project->id, '客户已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_staff'){
				$this->project->removeLabel($this->project->id, '职员已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_fee'){
				$this->project->removeLabel($this->project->id, '费用已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='apply_file'){
				$this->project->addLabel($this->project->id, '已申请归档');
				$this->project->update($this->project->id,array(
					'time_end'=>$this->date->today
				));
				$this->output->status='refresh';
				$this->output->message('归档申请已接受');
			}
			
			elseif($submit=='review_finance'){
				$this->project->addLabel($this->project->id, '通过财务审核');
				$this->output->status='refresh';
				$this->output->message('结案财务状况已经审核');
			}
			
			elseif($submit=='review_info'){
				$this->project->addLabel($this->project->id, '通过信息审核');
				$this->output->status='refresh';
				$this->output->message('案件信息已经审核');
			}
			
			elseif($submit=='review_manager'){
				$this->project->addLabel($this->project->id, '通过主管审核');
				$this->project->update($this->project->id,array(
					'time_end'=>$this->date->today,
				));
				$this->output->status='refresh';
				$this->output->message('案件已经审核，已正式归档');
			}
			
			elseif(!in_array('咨询',$labels) && $submit=='file'){
				$this->project->removeLabel($this->project->id, '已申请归档');
				$this->project->addLabel($this->project->id, '案卷已归档');
				$this->output->status='refresh';
				$this->output->message('案卷归档归档完成');
			}

			elseif($submit=='apply_case_num'){
				
				$labels=$this->input->sessionPost('labels');
				
				if(!$labels['领域']){
					$this->output->message('获得案号前要先选择案件领域','warning');
					throw new Exception();
				}

				if(!$labels['分类']){
					$this->output->message('获得案号前要先选择案件分类','warning');
					throw new Exception();
				}

				$data=array(
					'num'=>$this->project->getNum($this->project->id, $labels['分类'], $labels['领域'], in_array('咨询', $labels), $project['first_contact'], $project['time_contract']),
				);
				
				$labels[]='类型已锁定';
				
				$this->project->update($this->project->id,$data);
				
				$this->project->updateLabels($this->project->id, $labels);
				
				$this->output->status='refresh';
			}

			if(is_null($this->output->status)){
				$this->output->status='success';
			}

		}catch(Exception $e){
			$this->output->status='fail';
		}
	}
	
	function match(){

		$term=$this->input->post('term');

		$result=$this->project->getListByPeople($this->user->id);//只匹配到当前用户参与的案件

		$array=array();

		foreach ($result as $row){
			if(strpos($row['case_name'], $term)!==false){
				$array[]=array(
					'label'=>strip_tags($row['case_name']).' - '.$row['num'],
					'value'=>$row['id']
				);
			}
		}
		
		$this->output->data=$array;
	}
}
?>