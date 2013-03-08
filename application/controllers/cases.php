<?php
class Cases extends SS_controller{
	
	var $form_validation_rules=array();
	
	function __construct(){
		parent::__construct();
	}
	
	function host(){
		$this->lists('host');
	}
	
	function consultant(){
		$this->lists('consultant');
	}
	
	function etc(){
		$this->lists('etc');
	}
	
	function file(){
		$this->lists('file');
	}
	
	function review(){
		$this->lists('review');
	}
	
	function index($para=NULL){
		$this->output->setData('案件', 'name');

		$field=array(
			'time_contract'=>array(
				'heading'=>array('data'=>'案号','width'=>'140px'),
				'cell'=>array('data'=>'{num}','title'=>'立案时间：{time_contract}')
			),
			'name'=>array('heading'=>'案名','cell'=>'{name}'),
			'lawyers'=>array('heading'=>array('data'=>'主办律师','width'=>'100px')),
			'schedule_grouped.time_start'=>array('heading'=>'最新日志','eval'=>true,'cell'=>"
				return '<span class=\"create-schedule\" case=\"{id}\">+</span> <a href=\"#schedule/lists?case={id}\" title=\"{schedule_name}\">'.str_getSummary('{schedule_name}').'</a>';
			"),
			'plan_grouped.time_start'=>array('heading'=>'最近提醒','eval'=>true,'cell'=>"
				return '<span class=\"create-schedule\" case=\"{id}\" completed=\"0\">+</span> {plan_time} <a href=\"#schedule/list/plan?case={id}\" title=\"{plan_name}\">'.str_getSummary('{plan_name}').'</a>';
			"),
			'is_reviewed'=>array('heading'=>array('data'=>'状态','width'=>'70px'),'eval'=>true,'cell'=>"
				return \$this->cases->getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			")
		);
		
		if(!$this->user->isLogged('lawyer') && $this->user->isLogged('finance')){
			$field=array(
				'time_contract'=>array('heading'=>array('data'=>'案号','width'=>'140px'),'cell'=>array('data'=>'{num}','title'=>'立案时间：{time_contract}')),
				'name'=>array('heading'=>'案名','cell'=>'{name}'),
				'lawyers'=>array('heading'=>array('data'=>'主办律师','width'=>'100px')),
				'is_reviewed'=>array('heading'=>array('data'=>'状态','width'=>'75px'),'eval'=>true,'cell'=>"
					return \$this->cases->getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
				")
			);

		}
		
		$table=$this->table->setFields($field)
			->setRowAttributes(array('hash'=>'cases/edit/{id}'))
			->setData($this->cases->getList($para))
			->generate();
		$this->load->addViewData('list',$table);
		$this->load->view('list');
	}
	
	function add(){
		$this->cases->id=$this->cases->add();
		$this->output->status='redirect';
		$this->output->data='cases/edit/'.$this->cases->id;
	}
	
	/**
	 * 获得一种项目的编辑页子表，如案件的相关客户列表
	 * 生成的html表格提供“载入到视图”（适用于edit方法内载入）和“返回”（适用于ajax局部刷新时使用）两种方式
	 * @param type $item 子项目名，如client,document,schedule
	 * @param type $case_id 指定案号，默认false，即在edit方法内调用，因为已知$this->cases->id，所以不用指定
	 * @param type $para 需要传递给subList中的列表程序的参数名和参数值。一般情况下只有在ajax局部刷新时才需要设定
	 * @return当$case_id==false（默认）时，无返回值，html表格作为字符串变量加载到视图
	 *	当$case_id为整数时，html表格作为字符串返回
	 */
	function subList($item,$case_id=false,$para=array()){
		if($case_id){
			$case=$this->cases->fetch($case_id);
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
				->generate($this->cases->getClientList($this->cases->id));
		}
		elseif($item=='staff'){
			if(!isset($para['timing_fee'])){
				$para['timing_fee']=$case['timing_fee'];
			}
			
			if(!isset($para['staff_lock'])){
				$para['staff_lock']=$case['staff_lock'];
			}
			
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
			if($para['timing_fee']){
				$fields['hourly_fee']=array('heading'=>'计时收费小时费率');
			}
			$list=$this->table->setFields($fields)
				->setAttribute('name',$item)
				->generate($this->cases->getStaffList($this->cases->id));

		}
		elseif($item=='fee'){
			if(!isset($para['fee_lock'])){
				$para['fee_lock']=$case['fee_lock'];
			}
			
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
					->generate($this->cases->getFeeList($this->cases->id));
		}
		elseif($item=='miscfee'){
			if(!isset($para['fee_lock'])){
				$para['fee_lock']=$case['fee_lock'];
			}
			
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
					->generate($this->cases->getFeeMiscList($this->cases->id));
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
					->generate($this->cases->getScheduleList($this->cases->id));
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
					->generate($this->cases->getPlanList($this->cases->id));
		}
		elseif($item=='document'){
			$this->load->model('document_model','document');
			if(!isset($para['apply_file'])){
				$para['apply_file']=$case['apply_file'];
			}
			
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
				'name'=>array('heading'=>array('data'=>'文件名','width'=>'150px'),'wrap'=>array('mark'=>'a','href'=>'/document/download/{id}')),
				'type'=>array('heading'=>array('data'=>'类型','width'=>'80px')),
				'comment'=>array('heading'=>'备注'),
				'time'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'eval'=>true,'cell'=>"
					return date('m-d H:i',{time});
				"),
				'username'=>array('heading'=>array('data'=>'上传人','width'=>'90px'))
			);
			if($para['apply_file']){
				array_splice($fields,0,0,array(
					'id'=>array('heading'=>array('data'=>'','width'=>'37px'),'cell'=>'<input type="checkbox" name="case_document_check[{document}]" checked="checked" />')
				));
			}
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->cases->getDocumentList($this->cases->id));
		}
		
		if(!$case_id){//没有指定$case_id，是在edit方法内调用
			$this->load->addViewData($item.'_list', $list);
		}else{
			return array('selector'=>'.item[name="'.$item.'"]>.contentTable','content'=>$list,'type'=>'html','method'=>'replace','content_name'=>'content-table');
		}
	}

	function edit($id){
		$this->cases->id=$id;
		
		$this->load->model('staff_model','staff');
		$this->load->model('client_model','client');
		$this->load->model('schedule_model','schedule');

		try{
			$cases=array_merge($this->cases->fetch($id),$this->input->sessionPost('cases'));

			$labels=array_merge($this->cases->getLabels($this->cases->id),$this->input->sessionPost('labels'));

			if(!$cases['name']){
				$this->output->setData('未命名案件','name');
			}else{
				$this->output->setData($cases['name'], 'name');
			}

			$case_role=$this->cases->getRoles($this->cases->id);

			$responsible_partner=$this->cases->getPartner($case_role);
			//获得本案督办人的id

			$lawyers=$this->cases->getLawyers($case_role);
			//获得本案办案人员的id

			$my_roles=$this->cases->getMyRoles($case_role);
			//本人的本案职位

			$this->load->addViewArrayData(compact('cases','labels','case_role','responsible_partner','lawyers','my_roles'));

			//计算本案有效日志总时间
			$this->load->view_data['schedule_time']=$this->schedule->calculateTime($this->cases->id);

			$this->load->view_data['case_status']=$this->cases->getStatusById($this->cases->id);

			$this->load->view_data['case_type_array']=array('诉前','一审','二审','再审','执行','劳动仲裁','商事仲裁');

			if(post('cases/is_query')){
				$this->load->view_data['staff_role_array']=array('督办人','接洽律师','律师助理');
			}else{
				$this->load->view_data['staff_role_array']=array('案源人','督办人','接洽律师','主办律师','协办律师','律师助理');
			}

			if($cases['timing_fee']){
				$this->load->view_data['case_fee_timing_string']=$this->cases->getTimingFeeString($this->cases->id);
			}

			$this->subList('client',false,array('client_lock'=>$cases['client_lock']));

			$this->subList('staff',false,array('staff_lock'=>$cases['staff_lock'],'timing_fee'=>$cases['timing_fee']));

			$this->subList('fee',false,array('fee_lock'=>$cases['fee_lock']));

			$this->subList('miscfee',false,array('fee_lock'=>$cases['fee_lock']));

			$this->subList('schedule');

			$this->subList('plan');

			$this->subList('document',false,array('apply_file'=>$cases['apply_file']));

			$this->load->view('cases/edit');
			
			$this->load->view('cases/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}

	}

	function submit($submit,$id,$button_id=NULL){
		
		$this->cases->id=$id;
		
		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
		//$case是用来汇总，读的，因此尽可能获取最新的，最多的信息。post('cases')是要写入数据库的，只是要更改的部分。
		$case=array_merge($this->cases->fetch($id),$this->input->sessionPost('cases'));
		$labels=array_merge($this->cases->getLabels($this->cases->id),$this->input->sessionPost('labels'));
		
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
				unset($_SESSION[CONTROLLER]['post'][$this->cases->id]);
				$this->output->status='close';
			}
		
			elseif($submit=='cases'){

				if(!$case['num']){
					$this->output->message('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
					throw new Exception();
				}
				if(isset($labels['分类']) && in_array($labels['分类'],array('诉讼','非诉讼')) && !$case['is_query'] && !$case['focus']){
					$this->output->message('请填写案件争议焦点','warning');
					throw new Exception;
				}
				
				$this->cases->update($this->cases->id,post('cases'));
				$this->cases->updateLabels($this->cases->id,post('labels'));
				
				unset($_SESSION['cases']['post'][$this->cases->id]);
				$this->output->status='close';
			}
			
			elseif($submit=='case_client'){
				
				//这样对数组做加法，后者同名键不会替换前者，即后者是前者的补充，而非更新
				$case_client=$this->input->sessionPost('case_client');
				$client=$this->input->sessionPost('client');
				$client_profiles=$this->input->sessionPost('client_profiles');
				$client_labels=$this->input->sessionPost('client_labels');
				
				if(!$case_client['role']){
					$this->output->message('请选择本案地位','warning');
					throw new Exception;
				}
		
				if($case_client['client']){//autocomplete搜索到已有客户
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
					
					$case_client['client']=$this->client->add($new_client);

					$this->output->message(
						'<a href="#'.
						($client['type']=='客户'?'client':'contact').
						'/edit/'.$case_client['client'].'">新'.
						$client['type'].' '.$client['name'].
						' 已经添加，点击编辑详细信息</a>'
					);
				}

				if($this->cases->addPeople($this->cases->id,$case_client['client'],'客户',$case_client['role'])){
					$this->output->setData($this->subList('client',$this->cases->id));
				}else{
					$this->output->message('客户添加错误', 'warning');
					throw new Exception;
				}
			}

			elseif($submit=='remove_people'){
				if($this->cases->removePeople($this->cases->id,$button_id)){
					$this->output->setData($this->subList('client',$this->cases->id));
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
				
				$case_role=$this->cases->getRoles($this->cases->id);
		
				$responsible_partner=$this->cases->getPartner($case_role);
				//获得本案督办人的id
				$my_roles=$this->cases->getMyRoles($case_role);
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

				if($this->cases->addStaff($this->cases->id,post('staff/id'),post('staff/role'),post('staff/hourly_fee'))){
					$this->output->setData($this->subList('staff',$this->cases->id));
					unset($_SESSION['cases']['post'][$this->cases->id]['staff']['id']);
				}else{
					$this->output->message('人员添加错误', 'warning');
				}

				$this->cases->calcContribute($this->cases->id);
			}
			
			elseif($submit=='remove_staff'){
				if($this->cases->removePeople($this->cases->id,$button_id)){
					$this->output->setData($this->subList('staff',$this->cases->id));
				}
			}
			
			elseif($submit=='case_fee'){
				
				$case_fee=$this->input->sessionPost('case_fee');

				if(!$case_fee['type']){
					$this->output->message('请选择收费类型','warning');
				}
				
				if(!is_numeric($case_fee['fee'])){
					$this->output->message('请预估收费金额（数值）','warning');
				}
				
				if(!$case_fee['pay_date']){
					$this->output->message('请预估收费时间','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception;
				}
				
				if($this->cases->addFee($this->cases->id,$case_fee['fee'],$case_fee['pay_date'],$case_fee['type'],$case_fee['condition'])){
					//unset($_SESSION['cases']['post']['case_fee']);
					$this->output->setData($this->subList('fee',$this->cases->id));
				}else{
					$this->output->message('收费添加错误', 'warning');
				}
			}
			
			elseif($submit=='remove_fee' || $submit=='remove_miscfee'){
				$this->cases->removeFee($this->cases->id,$button_id);
				
				if($submit=='remove_fee'){
					$this->output->setData($this->subList('fee',$this->cases->id));
				}else{
					$this->output->setData($this->subList('miscfee',$this->cases->id));
				}
			}
			
			elseif($submit=='case_fee_review' && $this->user->isLogged('finance')){
				//财务审核
				$this->cases->ignoreFee($fee_ids);
			}
			
			elseif($submit=='case_fee_timing'){
				
				$timing_fee=$this->input->sessionPost('case_fee_timing');

				if(!$timing_fee){
					$this->cases->removeTimingFee($this->cases->id);
				}

				if(
					post('cases/timing_fee') && 
					(!$timing_fee['date_start'] || $timing_fee['included_hours']==='')
				){
					$this->output->message('账单起始日或包含小时数未填','warning');
					throw new Exception();

				}else{
					if($this->cases->setTimingFee($this->cases->id,$timing_fee['date_start'],$timing_fee['bill_day'],$timing_fee['payment_day'],$timing_fee['included_hours'],$timing_fee['payment_cycle'],$timing_fee['contract_cycle'])){
						unset($_SESSION['cases']['post'][$this->cases->id]['case_fee_timing']);
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
				
				if($this->cases->addFee($this->cases->id,$misc_fee['fee'],$misc_fee['pay_date'],'办案费',NULL,$misc_fee['receiver'],$misc_fee['comment'])){
					$this->output->setData($this->subList('miscfee',$this->cases->id));
				}else{
					$this->output->message('收费添加错误', 'warning');
				}
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
				
				$document=$this->upload->data();
				
				if(!$document_labels['类型']){
					$this->output->message('请选择文件类型','warning');
					throw new Exception;
				}
				
				$document['name']=$document['client_name'];
				$document['size']=$document['file_size'];
				$document['extname']=$document['file_ext'];
				$document['type']=$document['file_type'];
				
				$document['id']=$this->document->add($document);
				
				$this->cases->addDocument($this->cases->id, $document['id']);
				
				rename($this->config->item('document_path').$document['file_name'], $this->config->item('document_path').$document['id']);
				
				$this->output->setData($this->subList('document', $this->cases->id));
				
				unset($_SESSION['cases']['post'][$this->cases->id]['case_document']);
			}
			
			elseif($submit=='remove_document'){
				if($this->cases->removeDocument($this->cases->id,$button_id)){
					$this->output->setData($this->subList('document',$this->cases->id));
				}
			}
			
			elseif($submit=='file_document_list'){
				
				$this->load->model('document_model','document');

				$document_catalog=$this->cases->getDocumentCatalog($this->cases->id,post('case_document_check'));

				$this->load->view('case/document_catalog');
			}
			
			elseif($submit=='new_case'){
				$this->cases->update($this->cases->id,array(
					'is_query'=>false,
					'filed'=>false,
					'num'=>NULL,
					'time_contract'=>$this->config->item('date'),
					'time_end'=>date('Y-m-d',$this->config->item('timestamp')+100*86400)
				));
				
				$this->output->message('已立案，请立即获得案号');
				
				$this->output->status='refresh';
			}
			
			elseif($submit=='file' && $case['is_query']){
				$this->cases->update($this->cases->id,array('filed'=>true));
				$this->output->status='refresh';
				$this->output->message('咨询案件已归档');
			}
			
			elseif($submit=='review'){
				$this->cases->update($this->cases->id,array('is_reviewed'=>true));
				$this->output->status='refresh';
				$this->output->message('通过立案审核');
			}
			
			elseif($submit=='apply_lock'){
				//申请锁定，发送一条消息给督办人
				if($responsible_partner){
					$apply_lock_message=$this->user->name.'申请锁定'.strip_tags($case['name']).'一案，[url=http://sys.lawyerstars.com/#cases/edit/'.$this->cases->id.']点此进入[/url]';
					$this->user->sendMessage($responsible_partner,$apply_lock_message,'caseLockApplication');//imperfect
					$this->output->message('锁定请求已经发送至本案督办人');
				}else{
					$this->output->message('本案没有督办人，无处发送申请','warning');
				}
			}
			
			elseif($submit=='lock_type'){
				$this->cases->update($this->cases->id,array('type_lock'=>true));
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_client'){
				$this->cases->update($this->cases->id,array('client_lock'=>true));
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_lawyer'){
				$this->cases->update($this->cases->id,array('staff_lock'=>true));
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_fee'){
				$this->cases->update($this->cases->id,array('fee_lock'=>true));
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_client'){
				$this->cases->update($this->cases->id,array('client_lock'=>false));
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_lawyer'){
				$this->cases->update($this->cases->id,array('staff_lock'=>false));
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_fee'){
				$this->cases->update($this->cases->id,array('fee_lock'=>false));
				$this->output->status='refresh';
			}
			
			elseif($submit=='apply_file'){
				$this->cases->update($this->cases->id,array(
					'apply_file'=>true,
					'time_end'=>$this->config->item('date')
				));
				$this->output->status='refresh';
				$this->output->message('归档申请已接受');
			}
			
			elseif($submit=='review_finance'){
				$this->cases->update($this->cases->id,array('finance_review'=>true));
				$this->output->status='refresh';
				$this->output->message('结案财务状况已经审核');
			}
			
			elseif($submit=='review_info'){
				$this->cases->update($this->cases->id,array('info_review'=>true));
				$this->output->status='refresh';
				$this->output->message('案件信息已经审核');
			}
			
			elseif($submit=='review_manager'){
				$this->cases->update($this->cases->id,array(
					'time_end'=>$this->config->item('date'),
					'manager_review'=>true
				));
				$this->output->status='refresh';
				$this->output->message('案件已经审核，已正式归档');
			}
			
			elseif(!$case['is_query'] && $submit=='file'){
				$this->cases->update($this->cases->id,array('filed'=>true));
				$this->cases->updateFileStatus($this->cases->id,'在档');
				$this->output->status='refresh';
				$this->output->message('案件实体归档完成');
			}

			elseif($submit=='apply_case_num'){
				
				$labels=$this->input->post('labels');
				
				if(!$labels['领域']){
					$this->output->message('获得案号前要先选择案件领域','warning');
					throw new Exception();
				}

				if(!$labels['分类']){
					$this->output->message('获得案号前要先选择案件分类','warning');
					throw new Exception();
				}

				$data=array(
					'num'=>$this->cases->getNum($this->cases->id, $labels['分类'], $labels['领域'], $case['is_query'], $case['first_contact'], $case['time_contract']),
					'type_lock'=>1,
				);
				
				$this->cases->update($this->cases->id,$data);
				
				$this->cases->updateLabels($this->cases->id, $labels);
				
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

		$result=$this->cases->getListByPeople($this->user->id);//只匹配到当前用户参与的案件

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