<?php
class Cases extends SS_controller{
	function __construct(){
		$this->default_method='lists';
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
	
	function lists($para=NULL){
		$this->output->setData('案件', 'name');

		$field=array(
			'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','td'=>'title="立案时间：{time_contract}" href="cases/edit/{id}"','content'=>'{num}'),
			'name'=>array('title'=>'案名','content'=>'{name}'),
			'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
			'schedule_grouped.time_start'=>array('title'=>'最新日志','eval'=>true,'content'=>"
				return '<a href=\"javascript:showWindow(\'schedule/add?case={id}\')\">+</a> <a href=\"/schedule/lists?case={id}\" title=\"{schedule_name}\">'.str_getSummary('{schedule_name}').'</a>';
			"),
			'plan_grouped.time_start'=>array('title'=>'最近提醒','eval'=>true,'content'=>"
				return '<a href=\"javascript:showWindow(\'schedule/add?case={id}&completed=0\')\">+</a> {plan_time} <a href=\"/schedule/list/plan?case={id}\" title=\"{plan_name}\">'.str_getSummary('{plan_name}').'</a>';
			"),
			'is_reviewed'=>array('title'=>'状态','td_title'=>'width="75px"','eval'=>true,'content'=>"
				return \$this->cases->getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			",'orderby'=>false)
		);
		
		if(!$this->user->isLogged('lawyer') && $this->user->isLogged('finance')){
			$field=array(
				'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','td'=>'title="立案时间：{time_contract}"','content'=>'<a href="/cases/edit/{id}">{num}</a>'),
				'name'=>array('title'=>'案名','content'=>'{name}'),
				'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
				'is_reviewed'=>array('title'=>'状态','td_title'=>'width="75px"','eval'=>true,'content'=>"
					return \$this->cases->getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
				",'orderby'=>false)
			);

		}
		
		$table=$this->table->setFields($field)
			->setData($this->cases->getList($para))
			->generate();
		$this->load->addViewData('list',$table);
		$this->load->view('list');
	}
	
	function add(){
		$this->edit();
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
			$case=$this->cases->getPostData($case_id);
		}
		
		if($item=='client'){
			if(!isset($para['client_lock'])){
				$para['client_lock']=$case['client_lock'];
			}
		
			$fields=array(
				'name'=>array('title'=>'名称','eval'=>true,'content'=>"
					\$return='';
					if(!post('cases/client_lock')){
						\$return.='<input type=\"checkbox\" name=\"case_client_check[]\" value=\"{id}\" />';
					}
					\$return.='<a href=\"javascript:showWindow(\''.('{type}'=='客户'?'client':'contact').'/edit/{people}\')\">{name}</a>';
					return \$return;
				",'orderby'=>false),
				'phone'=>array('title'=>'电话','td'=>'class="ellipsis" title="{phone}"'),
				'email'=>array('title'=>'电邮','wrap'=>array('mark'=>'a','href'=>'mailto:{email}','title'=>'{email}','target'=>'_blank'),'td'=>'class="ellipsis"'),
				'role'=>array('title'=>'本案地位','orderby'=>false),
				'type'=>array('title'=>'类型','td_title'=>'width="60px"','orderby'=>false)
			);
			if(!$para['client_lock']){
				//客户锁定时不显示删除按钮
				$fields['name']['title']='<input type="submit" name="submit[case_client_delete]" value="删" />'.$fields['name']['title'];
			}
			$list=$this->table->setFields($fields)
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
				'staff_name'=>array('title'=>'名称','content'=>'{staff_name}','orderby'=>false),
				'role'=>array('title'=>'本案职位','orderby'=>false),
				'contribute'=>array('title'=>'贡献','eval'=>true,'content'=>"
					\$hours_sum_string='';
					if('{hours_sum}'){
						\$hours_sum_string='<span class=\"right\">{hours_sum}小时</span>';
					}

					return \$hours_sum_string.'<span>{contribute}'.('{contribute_amount}'?' ({contribute_amount})':'').'</span>';
				",'orderby'=>false)
			);
			if($para['timing_fee']){
				$fields['hourly_fee']=array('title'=>'计时收费小时费率','td'=>'class="editable" id="{id}"','orderby'=>false);
			}
			if(!$para['staff_lock']){
				//律师锁定时不显示删除按钮
				$fields['staff_name']['title']='<input type="submit" name="submit[staff_delete]" value="删" />'.$fields['staff_name']['title'];
				$fields['staff_name']['content']='<input type="checkbox" name="staff_check[]" value="{id}">'.$fields['staff_name']['content'];
			}
			$list=$this->cases->table->setFields($fields)
				->setAttribute('name',$item)
				->generate($this->cases->getStaffList($this->cases->id));

		}
		elseif($item=='fee'){
			if(!isset($para['fee_lock'])){
				$para['fee_lock']=$case['fee_lock'];
			}
			
			$fields=array(
				'type'=>array('title'=>'类型','td'=>'id="{id}"','content'=>'{type}','orderby'=>false),
				'fee'=>array('title'=>'数额','eval'=>true,'content'=>"
					\$return='{fee}'.('{fee_received}'==''?'':' <span title=\"{fee_received_time}\">（到账：{fee_received}）</span>');
					if('{reviewed}'){
						\$return=wrap(\$return,array('mark'=>'span','style'=>'color:#AAA'));
					}
					return \$return;
				",'orderby'=>false),
				'condition'=>array('title'=>'条件','td'=>'class="ellipsis" title="{condition}"','orderby'=>false),
				'pay_date'=>array('title'=>'预计时间','orderby'=>false)
			);
			if(!$para['fee_lock']){
				$fields['type']['title']='<input type="submit" name="submit[case_fee_delete]" value="删" />'.$fields['type']['title'];
			}
			if(!$para['fee_lock'] || $this->user->isLogged('finance')){
				$fields['type']['content']='<input type="checkbox" name="case_fee_check[]" value="{id}">'.$fields['type']['content'];
			}
			$list=$this->cases->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->cases->getFeeList($this->cases->id));
		}
		elseif($item=='miscfee'){
			if(!isset($para['fee_lock'])){
				$para['fee_lock']=$case['fee_lock'];
			}
			
			$fields=array(
				'receiver'=>array('title'=>'收款方','orderby'=>false),
				'fee'=>array('title'=>'数额','eval'=>true,'content'=>"
					return '{fee}'.('{fee_received}'==''?'':' （到账：{fee_received}）');
				",'orderby'=>false),
				'comment'=>array('title'=>'备注','orderby'=>false),
				'pay_date'=>array('title'=>'预计时间','orderby'=>false)
			);
			if(!$para['fee_lock']){
				$fields['receiver']['title']='<input type="submit" name="submit[case_fee_misc_delete]" value="删" />'.$fields['receiver']['title'];
				$fields['receiver']['content']='<input type="checkbox" name="case_fee_check[]" value="{id}" />{receiver}';
			}
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->cases->getFeeMiscList($this->cases->id));
		}
		elseif($item=='schedule'){
			$fields=array(
				'name'=>array('title'=>'标题','td_title'=>'width="150px"','wrap'=>array('mark'=>'a','href'=>'javascript:showSchedule({id})'),'orderby'=>false),
				'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
					return date('m-d H:i',{time_start});
				",'orderby'=>false),
				'username'=>array('title'=>'填写人','td_title'=>'width="90px"','orderby'=>false)
			);
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->cases->getScheduleList($this->cases->id));
		}
		elseif($item=='plan'){
			$fields=array(
				'name'=>array('title'=>'标题','td_title'=>'width="150px"','wrap'=>array('mark'=>'a','href'=>'javascript:showWindow(\'schedule/edit/{id}\')'),'orderby'=>false),
				'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
					return date('m-d H:i',{time_start});
				",'orderby'=>false),
				'username'=>array('title'=>'填写人','td_title'=>'width="90px"','orderby'=>false)
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
					'title'=>'',
					'eval'=>true,
					'content'=>"
						if('{extname}'==''){
							\$image='folder';
						}elseif(is_file('web/images/file_type/{extname}.png')){
							\$image='{extname}.png';
						}else{
							\$image='unknown';
						}
						return '<img src=\"/images/file_type/'.\$image.'.png\" alt=\"{extname}\" />';
					",
					'td_title'=>'width="40px"',
					'orderby'=>false
				),
				'name'=>array('title'=>'文件名','td_title'=>'width="150px"','wrap'=>array('mark'=>'a','href'=>'/cases/documentdownload/{id}'),'orderby'=>false),
				'type'=>array('title'=>'类型','td_title'=>'width="80px"'),
				'comment'=>array('title'=>'备注','orderby'=>false),
				'time'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
					return date('m-d H:i',{time});
				"),
				'username'=>array('title'=>'上传人','td_title'=>'width="90px"')
			);
			if($para['apply_file']){
				array_splice($fields,0,0,array(
					'id'=>array('title'=>'','td_title'=>'width="37px"','content'=>'<input type="checkbox" name="case_document_check[{id}]" checked="checked" />')
				));
			}
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->document->getListByCase($this->cases->id));
		}
		
		if(!$case_id){//没有指定$case_id，是在edit方法内调用
			$this->load->addViewData($item.'_list', $list);
		}else{
			return array('selector'=>'.item[name="'.$item.'"]>.contentTable','content'=>$list,'type'=>'html','method'=>'replace');
		}
	}

	function edit($id=NULL){
		$this->load->model('staff_model','staff');
		$this->load->model('client_model','client');
		$this->load->model('schedule_model','schedule');

		$cases=$this->cases->getPostData($id);
		$labels=$this->cases->getLabels($this->cases->id);
		
		if($cases['name']){
			$this->output->setData(strip_tags($cases['name']), 'name');
		}

		$case_role=$this->cases->getRoles($this->cases->id);
		
		$responsible_partner=$this->cases->getPartner($case_role);
		//获得本案督办合伙人的id
		
		$lawyers=$this->cases->getLawyers($case_role);
		//获得本案办案人员的id
		
		$my_roles=$this->cases->getMyRoles($case_role);
		//本人的本案职位
		
		$this->load->addViewArrayData(compact('cases','labels','case_role','responsible_partner','lawyers','my_roles'));
		
		if($cases['client_lock']){
			post('case_client_extra/classification','联系人');
		}		

		//计算本案有效日志总时间
		$this->load->view_data['schedule_time']=$this->schedule->calculateTime($this->cases->id);
		
		$this->load->view_data['case_status']=$this->cases->getStatusById($this->cases->id);
		
		$this->load->view_data['case_type_array']=array('诉前','一审','二审','再审','执行','劳动仲裁','商事仲裁');
		
		if(post('cases/is_query')){
			$this->load->view_data['case_lawyer_role_array']=array('督办合伙人','接洽律师','接洽律师（次要）','律师助理');
		}else{
			$this->load->view_data['case_lawyer_role_array']=array('督办合伙人','信息提供（20%）','信息提供（10%）','接洽律师','接洽律师（次要）','律师助理','实际贡献');
		}
		
		$this->subList('client',false,array('client_lock'=>$cases['client_lock']));
		
		$this->subList('staff',false,array('staff_lock'=>$cases['staff_lock'],'timing_fee'=>$cases['timing_fee']));
		
		$this->subList('fee',false,array('fee_lock'=>$cases['fee_lock']));
		
		if($cases['timing_fee']){
			$this->load->view_data['case_fee_timing_string']=$this->cases->getTimingFeeString($this->cases->id);
			if(post('case_timing_fee/time_start')){
				post('case_timing_fee_extra/time_start',date('Y-m-d',post('case_timing_fee/time_start')));
			}
		}
		
		$this->subList('miscfee',false,array('fee_lock'=>$cases['fee_lock']));
		
		$this->subList('schedule');
		
		$this->subList('plan');
		
		$this->subList('document',false,array('apply_file'=>$cases['apply_file']));
		
		$this->load->view('cases/edit');
		
	}

	function submit($submit,$id){
		
		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
		//$case是用来汇总，读的，因此尽可能获取最新的，最多的信息。post('cases')是要写入数据库的，只是要更改的部分。
		$case=array_merge($this->cases->getPostData($id),(array)post('cases'))+(array)$this->input->post('cases');
		
		$labels=$this->cases->getLabels($this->cases->id)+(array)post('labels');
		
		try{
		
			if($submit=='cancel'){
				unset($_SESSION[CONTROLLER]['post'][$this->cases->id]);
				$this->cases->clearUserTrash();
			}
		
			elseif($submit=='cases'){

				//TODO 不科学
				$case_client_role = $this->cases->getClientRole($this->cases->id);

				//根据案件类别和客户、相对方更新案名
				//TODO案名更新尚有问题
				if(isset($case_client_role['client']) && !$case['filed']){
					$case_client_role['client_name']='<a href="#client/edit/'.$case_client_role['client'].'">'.$case_client_role['client_name'].'</a>';

					$case_client_role['opposite_name']='<a href="#client/edit/'.$case_client_role['opposite'].'">'.$case_client_role['opposite_name'].'</a>';

					post('cases/name',$this->cases->getName($case_client_role,$case['is_query'],@$labels['分类'],@$labels['领域'],$case['name_extra']));

				}

				if(!$case['num']){
					$this->output->message('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
					throw new Exception();
				}
				if($labels['分类']!='法律顾问' && !$case['is_query'] && !$case['focus']){
					$this->output->message('请填写案件争议焦点','warning');
					throw new Exception();
				}
				
				$this->cases->update($this->cases->id,post('cases'));
				$this->cases->updateLabels($this->cases->id,post('labels'));
				
				unset($_SESSION['cases']['post'][$this->cases->id]);
			}
			
			elseif($submit=='case_client'){
				
				$case_client=post('case_client')+$this->input->post('case_client');
				$case_client_extra=post('case_client_extra')+$this->input->post('case_client_extra');
				
				if($case_client['client']){//autocomplete搜索到已有客户
					$this->output->message("系统中已经存在{$case_client_extra['client_name']}，已自动识别");
				}
				else{//添加新客户
					$new_client=array(
						'name'=>$case_client_extra['client_name'],
						'character'=>isset($case_client_extra['character']) && $case_client_extra['character']=='单位'?'单位':'自然人',
						'type'=>$case_client_extra['classification'],
						'labels'=>array('类型'=>$case_client_extra['type'])
					);

					if($case_client_extra['classification']=='客户'){//客户必须输入来源
						$client_source=$this->client->setSource($case_client_extra['source_type'],$case_client_extra['source_detail']);

						$staff_check=$this->staff->check($case_client_extra['source_lawyer_name'],'id',false);

						if($staff_check<0){
							$this->output->message('请输入正确的来源律师','warning');
						}

						if($staff_check>0 && $client_source>0){
							$new_client['staff']=$staff_check;
							$new_client['source']=$client_source;
						}else{
							$this->output->message('客户来源识别错误','warning');
						}
					}else{//非客户必须输入工作单位
						if($case_client_extra['work_for']){
							$new_client['profiles']['工作单位']=$case_client_extra['work_for'];
						}else{
							$this->output->message('请输入工作单位','warning');
						}
					}
					
					if(!$case_client_extra['phone'] && !$case_client_extra['email']){
						$this->output->message('至少输入一种联系方式', 'warning');
					}

					if(isset($case_client_extra['phone'])){
						if($this->client->isMobileNumber($case_client_extra['phone'])){
							$new_client['profiles']['手机']=$case_client_extra['phone'];
						}else{
							$new_client['profiles']['电话']=$case_client_extra['phone'];
						}
					}

					if($case_client_extra['email']){
						$new_client['profiles']['电子邮件']=$case_client_extra['email'];
					}

					if($this->output->message['warning']){
						throw new Exception();
					}
					
					$new_client_id=$this->client->add($new_client);

					$case_client['client']=$new_client_id;

					$this->output->message(
						'<a href="javascript:showWindow(\''.
						($new_client['type']=='客户'?'client':'contact').
						'/edit/'.$case_client['client'].'\')" target="_blank">新'.
						$case_client_extra['classification'].' '.$case_client_extra['client_name'].
						' 已经添加，点击编辑详细信息</a>'
					);

				}

				if($this->cases->addPeople($this->cases->id,$case_client['client'],$case_client['role'])){
					//unset($_SESSION['cases']['post'][$this->cases->id]['case_client']);
					//unset($_SESSION['cases']['post'][$this->cases->id]['case_client_extra']);
					
					$this->output->setData($this->subList('client',$this->cases->id));
					$this->output->status='success';
				}
			}

			elseif($submit=='case_client_delete'){
				if($this->cases->removePeople($this->cases->id,$this->input->post('case_client_check'))){
					$this->output->setData($this->subList('client',$this->cases->id));
				}
			}
			
			elseif($submit=='staff'){
				
				$case_role=$this->cases->getRoles($this->cases->id);
		
				$responsible_partner=$this->cases->getPartner($case_role);
				//获得本案督办合伙人的id
				$my_roles=$this->cases->getMyRoles($case_role);
				//本人的本案职位

				if(post('staff/role')=='实际贡献' && !(in_array('督办合伙人',$my_roles) || in_array('主办律师',$my_roles))){
					//禁止非主办律师/合伙人分配实际贡献
					$this->output->message('你没有权限分配实际贡献');
					throw new Exception();

				}elseif(post('staff/contribute',$this->cases->lawyerRoleCheck($this->cases->id,post('staff/role'),post('staff_extra/actual_contribute')))===false){
					//检查并保存本条staff的contribute，若不可添加则返回false并终止过程
					throw new Exception();

				}else{
					post('staff/hourly_fee',(int)post('staff/hourly_fee'));
					
					if(!$responsible_partner && post('staff/role')!='督办合伙人'){
						//第一次插入督办合伙人后不显示警告，否则如果不存在督办合伙人则显示警告
						$this->output->message('未设置督办合伙人','warning');
					}
					
					if(is_null(post('staff/role'))){
						$this->output->message('未选择本案职务','warning');
						throw new Exception();
					}
					
					if($this->cases->addStaff($this->cases->id,post('staff/id'),post('staff/role'),post('staff/hourly_fee'))){
						//unset($_SESSION['cases']['post'][$this->cases->id]['staff']);
						//unset($_SESSION['cases']['post'][$this->cases->id]['staff_extra']);
						$this->output->setData($this->subList('staff',$this->cases->id));
					}
				}

				$this->cases->calcContribute($this->cases->id);
			}
			
			elseif($submit=='staff_delete'){
				if($this->cases->removePeople($this->cases->id,$this->input->post('staff_check'))){
					$this->output->setData($this->subList('staff',$this->cases->id));
				}
			}
			
			elseif($submit=='case_fee'){
				
				$case_fee=(array)post('case_fee')+$this->input->post('case_fee');

				if(!$case_fee['fee']){
					$this->output->message('请预估收费金额','warning');
				}
				
				if(!$case_fee['pay_date']){
					$this->output->message('请预估收费时间','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception();
				}
				
				if($this->cases->addFee($this->cases->id,$case_fee['fee'],$case_fee['pay_date'],$case_fee['type'],$case_fee['condition'])){
					//unset($_SESSION['cases']['post']['case_fee']);
					$this->output->setData($this->subList('fee',$this->cases->id));
				}
			}
			
			elseif($submit=='case_fee_delete' || $submit=='case_fee_misc_delete'){
				$this->cases->removeFee($this->input->post('case_fee_check'));
				
				if($submit=='case_fee_delete'){
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
				
				$timing_fee=(array)post('case_fee_timing')+$this->input->post('case_fee_timing');

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

				$this->output->setData('cases/edit/'.$this->cases->id, 'content', 'uri', 'form[name="cases"]','replace');
			}
			
			elseif($submit=='case_fee_misc'){
				
				$misc_fee=(array)post('case_fee_misc')+$this->input->post('case_fee_misc');
				
				if(!$misc_fee['receiver']){
					$this->output->message('未选择办案费收款方','warning');
				}
				
				if(!$misc_fee['fee']){
					$this->output->message('请填写办案费约定金额','warning');
				}
				
				if(!$misc_fee['pay_date']){
					$this->output->message('请填写收费时间','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception();
				}
				
				if($this->cases->addFee($this->cases->id,$misc_fee['fee'],$misc_fee['pay_date'],'办案费',NULL,$misc_fee['receiver'],$misc_fee['comment'])){
					//unset($_SESSION['cases']['post']['case_fee_misc']);
					$this->output->setData($this->subList('miscfee',$this->cases->id));
				}
			}
			
			elseif($submit=='case_document'){
				$this->load->model('document_model','document');
				
				$document=(array)post('document')+$this->input->post('document');
				
				$document_labels=(array)post('document_labels')+$this->input->post('document_labels');
				
				if(!isset($_FILES['file'])){
					$this->output->message('请上传文件','warning');
				}
				elseif($_FILES['file']['error']>0){
					$this->output->message('文件上传错误:'.$_FILES['file']['error'],'warning');
				}
				
				if(!$document_labels['类型']){
					$this->output->message('请选择文件类型','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception;
				}
				
				$document['type']=$this->document->getExtension($document['name']);
				
				$document['size']=$_FILES['file']['size'];
				
				$document['id']=$this->cases->addDocument($this->cases->id,$document['name'],$document['size']);

				$store_path=iconv("utf-8","gbk",$this->config->item('case_document_path')."/".$document['id']);//存储路径转码

				move_uploaded_file($_FILES['file']['tmp_name'], $store_path);

				unset($_SESSION['cases']['post']['case_document']);
			}
			
			elseif($submit=='file_document_list'){
				
				$this->load->model('document_model','document');

				$document_catalog=$this->cases->getDocumentCatalog($this->cases->id,post('case_document_check'));

				$this->load->view('case/document_catalog');
			}
			
			elseif($submit=='new_case'){
				post('cases/is_query',0);
				post('cases/filed',0);
				post('cases/num','');
				post('cases/time_contract',$this->config->item('date'));
				post('cases/time_end',date('Y-m-d',$this->config->item('timestamp')+100*86400));
				//默认签约时间和结案时间

				$this->output->message('已立案，请立即获得案号');
			}
			
			elseif(post('cases/is_query') && $submit=='file'){
				post('cases/filed',1);
				$this->output->message('咨询案件已归档');
			}
			
			elseif($submit=='send_message'){
				showMessage('本案已被退回');
				case_reviewMessage('被退回审核',$lawyers);
			}
			
			elseif($submit=='review'){
				post('cases/is_reviewed',1);
				showMessage('本案已经审核通过');
				$this->cases->reviewMessage('通过审核',$lawyers);
			}
			
			elseif($submit=='apply_lock'){
				//申请锁定，发送一条消息给督办合伙人
				if($responsible_partner){
					$apply_lock_message=$_SESSION['username'].'申请锁定'.strip_tags(post('cases/name')).'一案，[url=http://sys.lawyerstars.com/cases/edit/'.$this->cases->id.']点此进入[/url]';
					$this->user->sendMessage($responsible_partner,$apply_lock_message,'caseLockApplication');//imperfect
					$this->output->message('锁定请求已经发送至本案督办合伙人');
				}else{
					$this->output->message('本案没有督办合伙人，无处发送申请','warning');
				}
			}
			
			elseif($submit=='lock_type'){
				post('cases/type_lock',1);
			}
			
			elseif($submit=='lock_client'){
				post('cases/client_lock',1);
			}
			
			elseif($submit=='lock_lawyer'){
				post('cases/staff_lock',1);
			}
			
			elseif($submit=='lock_fee'){
				post('cases/fee_lock',1);
			}
			
			elseif($submit=='unlock_client'){
				post('cases/client_lock',0);
			}
			
			elseif($submit=='unlock_lawyer'){
				post('cases/staff_lock',0);
			}
			
			elseif($submit=='unlock_fee'){
				post('cases/fee_lock',0);
			}
			
			elseif($submit=='apply_file'){
				post('cases/time_end',$this->config->item('date'));
				post('cases/apply_file',1);
				showMessage('归档申请已接受');
			}
			
			elseif($submit=='review_finance'){
				post('cases/finance_review',1);
				showMessage('结案财务状况已经审核');
			}
			
			elseif($submit=='review_info'){
				post('cases/info_review',1);
				showMessage('案件信息已经审核');
			}
			
			elseif($submit=='review_manager'){
				post('cases/time_end',$this->config->item('date'));
				post('cases/manager_review',1);
				showMessage('案件已经审核，已正式归档');
			}
			
			elseif(!post('cases/is_query') && $submit=='file'){
				db_insert('file_status',array('case'=>$this->cases->id,'status'=>'在档','time'=>$this->config->item('timestamp')));
				post('cases/filed',1);
				showMessage('案件实体归档完成');
			}

			elseif($submit=='apply_case_num'){
				
				$label=$this->input->post('label');
				
				if(!isset($label['分类'])){
					$this->output->message('获得案号前要先选择案件分类','warning');
					throw new Exception();
				}

				if(!isset($label['领域'])){
					$this->output->message('获得案号前要先选择案件领域','warning');
					throw new Exception();
				}

				$data=array(
					'num'=>$this->cases->getNum($this->cases->id, $label['分类'], $label['领域'], $case['is_query'], $case['first_contact'], $case['time_contract']),
					'type_lock'=>1
				);

				$this->cases->update($this->cases->id,$data);
				
				$this->output->setData('cases/edit/'.$this->cases->id, 'content', 'uri', 'form[name="cases"]','replace');
			}

			$this->output->status='success';

		}catch(Exception $e){
			$this->output->status='fail';
		}
	}
	
	function documentDownload($case_document_id){
		$this->load->model('document_model','document');
		
		$q_case_document="SELECT * FROM case_document WHERE id='".$case_document_id."'";
		$r_case_document=db_query($q_case_document);
		$case_document=mysql_fetch_array($r_case_document);
		
		//适应各浏览器的文件输出
		$ua = $_SERVER["HTTP_USER_AGENT"];
		
		$filename = $case_document['name'];
		$encoded_filename = urlencode($filename);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);
		
		if($this->document->openInBrowser($case_document['type'])){
			header('Content-Type:'.$this->document->getMime($case_document['type']).';charset=utf-8');
		}else{
			header('Content-Type:application/octet-stream;charset=utf-8');
			header('Content-Disposition:attachment');
		}
		
		if(preg_match("/MSIE/", $ua)) {
			header('Content-Disposition:filename="'.$encoded_filename.'"');
		}else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition:filename*="utf8\'\''.$filename.'"');
		}else {
			header('Content-Disposition:filename="'.$filename.'"');
		}
		
		$path=iconv("utf-8","gbk",$this->config->item('case_document_path').'/'.$case_document_id);
		
		readfile($path);
		exit;
	}
}
?>