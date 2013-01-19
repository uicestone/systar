<?php
class Cases extends SS_controller{
	function __construct(){
		$this->default_method='lists';
		parent::__construct();
		$this->output->setData('案件', 'name');
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
	 * @return当$case_idfalse（默认）时，无返回值，html表格作为字符串变量加载到视图
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
				'lawyer_name'=>array('title'=>'名称','content'=>'{lawyer_name}','orderby'=>false),
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
				$fields['lawyer_name']['title']='<input type="submit" name="submit[case_lawyer_delete]" value="删" />'.$fields['lawyer_name']['title'];
				$fields['lawyer_name']['content']='<input type="checkbox" name="case_lawyer_check[{id}]">'.$fields['lawyer_name']['content'];
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
						\$return=wrap(\$return,array('mark'=>'span','style'=>'color:#080'));
					}
					return \$return;
				",'orderby'=>false),
				'condition'=>array('title'=>'条件','td'=>'class="ellipsis" title="{condition}"','orderby'=>false),
				'pay_time'=>array('title'=>'预计时间','eval'=>true,'content'=>"
					return date('Y-m-d',{pay_time});
				",'orderby'=>false
				)
			);
			if(!$para['fee_lock']){
				$fields['type']['title']='<input type="submit" name="submit[case_fee_delete]" value="删" />'.$fields['type']['title'];
			}
			if(!$para['fee_lock'] || $this->user->isLogged('finance')){
				$fields['type']['content']='<input type="checkbox" name="case_fee_check[{id}]" >'.$fields['type']['content'];
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
				'pay_time'=>array('title'=>'预计时间','eval'=>true,'content'=>"
					return date('Y-m-d',{pay_time});
				",'orderby'=>false
				)
			);
			if(!$para['fee_lock']){
				$fields['receiver']['title']='<input type="submit" name="submit[case_fee_delete]" value="删" />'.$fields['receiver']['title'];
				$fields['receiver']['content']='<input type="checkbox" name="case_fee_check[{id}]" />{receiver}';
			}
			$list=$this->table->setFields($fields)
					->setAttribute('name',$item)
					->generate($this->cases->getFeeMiscList($this->cases->id));
		}
		elseif($item=='schedule'){
			$fields=array(
				'name'=>array('title'=>'标题','td_title'=>'width="150px"','wrap'=>array('mark'=>'a','href'=>'javascript:showWindow(\'schedule/edit/{id}\')'),'orderby'=>false),
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
			if(!isset($para['apply_file'])){
				$para['apply_file']=$case['apply_file'];
			}
			
			$fields=array(
				'type'=>array(
					'title'=>'',
					'eval'=>true,
					'content'=>"
						if('{type}'==''){
							\$image='folder';
						}elseif(is_file('web/images/file_type/{type}.png')){
							\$image='{type}.png';
						}else{
							\$image='unknown';
						}
						return '<img src=\"/images/file_type/'.\$image.'.png\" alt=\"{type}\" />';
					",
					'td_title'=>'width="70px"',
					'orderby'=>false
				),
				'name'=>array('title'=>'文件名','td_title'=>'width="150px"','wrap'=>array('mark'=>'a','href'=>'/cases/documentdownload/{id}'),'orderby'=>false),
				'doctype'=>array('title'=>'类型','td_title'=>'width="80px"'),
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
					->generate($this->cases->getDocumentList($this->cases->id));
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

		$case=$this->cases->getPostData($id);
		
		if($case['name']){
			$this->output->setData(strip_tags($case['name']), 'name');
		}

		$case_role=$this->cases->getRoles($this->cases->id);
		
		$responsible_partner=$this->cases->getPartner($case_role);
		//获得本案督办合伙人的id
		
		$lawyers=$this->cases->getLawyers($case_role);
		//获得本案办案人员的id
		
		$my_roles=$this->cases->getMyRoles($case_role);
		//本人的本案职位
		
		$this->load->addViewArrayData(compact('case','case_role','responsible_partner','lawyers','my_roles'));
		
		if($case['client_lock']){
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
		
		$this->subList('client',false,array('client_lock'=>$case['client_lock']));
		
		//post('case_client_extra/classification','客户');
		
		if(post('cases/is_query')){
			post('case_client_extra/type','潜在客户');
		}else{
			post('case_client_extra/type','成交客户');//让案下客户添加默认为成交客户
		}
		
		$this->subList('staff',false,array('staff_lock'=>$case['staff_lock'],'timing_fee'=>$case['timing_fee']));
		
		$this->subList('fee',false,array('fee_lock'=>$case['fee_lock']));
		
		if(post('case_fee/pay_time')){
			post('case_fee_extra/pay_time',date('Y-m-d',post('case_fee/pay_time')));
		}
		
		if(post('cases/timing_fee')){
			$this->load->view_data['case_fee_timing_string']=$this->cases->getTimingFeeString($this->cases->id);
			if(post('case_timing_fee/time_start')){
				post('case_timing_fee_extra/time_start',date('Y-m-d',post('case_timing_fee/time_start')));
			}
		}
		
		$this->subList('miscfee',false,array('fee_lock'=>$case['fee_lock']));
		
		if(post('case_fee_misc/pay_time')){
			post('case_fee_misc_extra/pay_time',date('Y-m-d',post('case_fee_misc/pay_time')));
		}
		
		$this->subList('schedule');
		
		$this->subList('plan');
		
		$this->subList('document',false,array('apply_file'=>$case['apply_file']));
		
		$this->load->view('cases/edit');
		$this->load->main_view_loaded=true;
	}

	function submit($submit,$id){
		
		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
		$case=$this->cases->getPostData($id);
		
		if(is_array(post('cases'))){
			$case=array_merge($case,post('cases'));
		}
		
		if($submit=='cancel'){
			unset($_SESSION[CONTROLLER][$this->cases->id]['post']);
			$this->cases->clearUserTrash();
			$this->output->setData('uri',substr($this->session->userdata('last_list_action'),1));
			return;
		}
		
		$this->load->library('form_validation');
		
		$case_client_role = $this->cases->getClientRole($this->cases->id);

		try{
		
			if($submit=='case_client'){
				if(post('case_client/client')){//autocomplete搜索到已有客户
					$this->output->message('系统中已经存在 '.post('case_client_extra/client_name').'，已自动识别');
				}
				else{//添加新客户
					$new_client=array(
						'name'=>post('case_client_extra/client_name'),
						'character'=>post('case_client_extra/character')=='单位'?'单位':'自然人',
						'labels'=>array('classification'=>post('case_client_extra/classification'),'type'=>post('case_client_extra/type'))
					);

					if(post('case_client_extra/classification')=='客户'){//客户必须输入来源
						$client_source=$this->client->setSource(post('case_client_extra/source_type'),post('case_client_extra/source_detail'));

						$staff_check=$this->staff->check(post('case_client_extra/source_lawyer_name'),'id',false);

						if($staff_check<0){
							$this->output->message('请输入正确的来源律师','warning');
						}

						if($staff_check>0 && $client_source>0){
							$new_client['staff']=$staff_check;
							$new_client['source']=$client_source;
							if(!post('cases/source')){
								post('cases/source',$client_source);
							}
						}else{
							$this->output->message('客户来源识别错误','warning');
						}
					}else{//非客户必须输入工作单位
						if(post('case_client_extra/work_for')){
							$new_client['profiles']['工作单位']=post('case_client_extra/work_for');
						}else{
							$this->output->message('请输入工作单位','warning');
						}
					}
					
					if(!post('case_client_extra/phone') && !post('case_client_extra/email')){
						$this->output->message('至少输入一种联系方式', 'warning');
					}

					if(post('case_client_extra/phone')){
						if($this->client->isMobileNumber(post('case_client_extra/phone'))){
							$new_client['profiles']['手机']=post('case_client_extra/phone');
						}else{
							$new_client['profiles']['电话']=post('case_client_extra/phone');
						}
					}

					if(post('case_client_extra/email')){
						$new_client['profiles']['电子邮件']=post('case_client_extra/email');
					}

					if($this->output->message['warning']){
						throw new Exception();
					}
					
					$new_client_id=$this->client->add($new_client);

					post('case_client/client',$new_client_id);

					$this->output->message(
						'<a href="javascript:showWindow(\''.
						(post('case_client_extra/classification')=='客户'?'client':'contact').
						'/edit/'.post('case_client/client').'\')" target="_blank">新'.
						post('case_client_extra/classification').' '.post('case_client_extra/name').
						' 已经添加，点击编辑详细信息</a>'
					);

				}

				if($this->cases->addPeople($this->cases->id,post('case_client/client'),post('case_client/role'))){
					unset($_SESSION['cases']['post'][$this->cases->id]['case_client']);
					unset($_SESSION['cases']['post'][$this->cases->id]['case_client_extra']);
					
					$this->output->setData($this->subList('client',$this->cases->id));
					$this->output->status='success';
				}
			}

			elseif($submit=='case_client_delete'){
				if($this->cases->removePeople($this->cases->id,$this->input->post('case_client_check'))){
					$this->output->data=$this->subList('client',$this->cases->id);
				}
			}
			
			elseif($submit=='case_lawyer'){
				if(post('case_lawyer/role')=='实际贡献' && !(in_array('督办合伙人',$my_roles) || in_array('主办律师',$my_roles))){
					//禁止非主办律师/合伙人分配实际贡献
					showMessage('你没有权限分配实际贡献');
					$submitable=false;

				}elseif(post('case_lawyer/contribute',$this->cases->lawyerRoleCheck($this->cases->id,post('case_lawyer/role'),post('case_lawyer_extra/actual_contribute')))===false){
					//检查并保存本条case_lawyer的contribute，若不可添加则返回false并终止过程
					$submitable=false;

				}else{
					post('case_lawyer/hourly_fee',(int)post('case_lawyer/hourly_fee'));

					if(!$responsible_partner && post('case_lawyer/role')!='督办合伙人'){
						//第一次插入督办合伙人后不显示警告，否则如果不存在督办合伙人则显示警告
						showMessage('未设置督办合伙人','warning');
						$submitable=false;
					}
					if($submitable && $this->cases->addLawyer($this->cases->id,post('case_lawyer'))){
						unset($_SESSION['cases']['post'][$this->cases->id]['case_lawyer']);
						unset($_SESSION['cases']['post'][$this->cases->id]['case_lawyer_extra']);
						if(post('cases/is_reviewed') && post('case_lawyer/role')!='实际贡献' && !in_array('督办合伙人',$my_roles)){
							post('cases/is_reviewed',0);
							showMessage('案件关键信息已经更改，需要重新审核');
						}
					}
				}
				post('case_lawyer_extra/show_add_form',true);//无论是否插入成功，刷新后继续显示添加律师表单

				$this->cases->calcContribute($this->cases->id);
			}
			
			elseif($submit=='case_fee'){

				post('case_fee/pay_time',strtotime(post('case_fee_extra/pay_time')));

				if(!post('case_fee/fee')){
					showMessage('请预估收费金额','warning');

				}elseif(!post('case_fee/pay_time')){
					showMessage('请预估收费时间','warning');

				}else{		
					if($this->cases->addFee($this->cases->id,post('case_fee'))){
						unset($_SESSION['cases']['post']['case_fee']);
						if(post('cases/is_reviewed')){
							post('cases/is_reviewed',0);
							showMessage('案件关键信息已经更改，需要重新审核');
						}
					}
				}
			}
			
			elseif($submit=='case_fee_review' && $this->user->isLogged('finance')){
				//财务审核
				$condition = db_implode(post('case_fee_check'), $glue = ' OR ','id','=',"'","'", '`','key');
				$condition_account=db_implode(post('case_fee_check'), $glue = ' OR ','case_fee','=',"'","'", '`','key');

				if($this->db->update('case_fee',array('reviewed'=>1),$condition)){
					showMessage('已完成案件收费到账审核');
				}
			}
			
			elseif($submit=='case_fee_timing'){

				if($this->input->post('case_timing_fee')===false){
					post('cases/timing_fee',0);
					$q_delete_case_fee_timing="DELETE FROM case_fee_timing WHERE `case`='".$this->cases->id."'";
					$q_delete_case_fee_timing_type="DELETE FROM case_fee WHERE `case`='".$this->cases->id."' AND type='计时预付'";
					db_query($q_delete_case_fee_timing);
					db_query($q_delete_case_fee_timing_type);
				}

				post('case_fee_timing',array_trim(post('case_fee_timing')));

				post('case_fee_timing/case',$this->cases->id);

				post('case_fee_timing/time_start',strtotime(post('case_fee_timing_extra/time_start')));//imperfect 2012/5/23 uicestone

				post('case_fee_timing/uid',$this->user->id);
				post('case_fee_timing/time',$this->config->item('timestamp'));

				if(
					post('cases/timing_fee') && 
					(!post('case_fee_timing/time_start') || 
					post('case_fee_timing/included_hours')=='')
				){
					showMessage('账单起始日或包含小时数未填','warning');

				}else{
					if(db_insert('case_fee_timing',post('case_fee_timing'),true,true)){
						unset($_SESSION['cases']['post']['case_fee_timing']);
					}
				}
			}
			
			elseif($submit=='case_fee_misc'){

				post('case_fee_misc/case',$this->cases->id);

				post('case_fee_misc/type','办案费');

				post('case_fee_misc/pay_time',strtotime(post('case_fee_misc_extra/pay_time')));

				if(!post('case_fee_misc/fee')){
					showMessage('请填写办案费约定金额','warning');

				}elseif(!post('case_fee_misc/pay_time')){
					showMessage('请填写收费时间','warning');

				}else{		
					if(db_insert('case_fee',post('case_fee_misc'))){
						unset($_SESSION['cases']['post']['case_fee_misc']);
					}
				}
			}
			
			elseif($submit=='case_document'){
				$this->load->model('document_model','document');
				if(post('case_document/doctype')=='其他' && !post('case_document/doctype_other')){
					showMessage('文件类别选择“其他”，则必须填写具体类别','warning');

				}elseif($_FILES["file"]["error"]>0){
					showMessage('文件上传错误:'.$_FILES["file"]["error"],'warning');

				}else{
					post('case_document/name',$_FILES["file"]["name"]);
					post('case_document/type',$this->document->getExtension(post('case_document/name')));
					post('case_document/size',$_FILES["file"]['size']);

					post('case_document/id',$this->cases->addDocument($this->cases->id,post('case_document')));

					$store_path=iconv("utf-8","gbk",$this->config->item('case_document_path')."/".post('case_document/id'));//存储路径转码

					move_uploaded_file($_FILES['file']['tmp_name'], $store_path);
				}

				unset($_SESSION['cases']['post']['case_document']);
			}
			
			elseif($submit=='file_document_list'){
				
				$this->load->model('document_model','document');

				$document_catalog=$this->cases->getDocumentCatalog($this->cases->id,post('case_document_check'));

				$this->load->view('case/document_catalog');
			}
			
			elseif($submit=='case_lawyer_delete'){

				$condition = db_implode(post('case_lawyer_check'), $glue = ' OR ','id','=',"'","'", '`','key');

				$q="DELETE FROM case_lawyer WHERE (".$condition.")";

				db_query($q);

				$this->cases->calcContribute($this->cases->id);

				if(post('cases/is_reviewed')){
					post('cases/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
			
			elseif($submit=='case_fee_delete'){

				$condition = db_implode(post('case_fee_check'), $glue = ' OR ','id','=',"'","'", '`','key');

				db_delete('case_fee',$condition);

				if(post('cases/is_reviewed')){
					post('cases/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
			
			elseif($submit=='new_case'){
				post('cases/is_query',0);
				post('cases/filed',0);
				post('cases/num','');
				post('cases/time_contract',$this->config->item('date'));
				post('cases/time_end',date('Y-m-d',$this->config->item('timestamp')+100*86400));
				//默认签约时间和结案时间

				showMessage('已立案，请立即获得案号');
			}
			
			elseif(post('cases/is_query') && $submit=='file'){
				post('cases/filed',1);
				showMessage('咨询案件已归档');
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
					showMessage('锁定请求已经发送至本案督办合伙人');
				}else{
					showMessage('本案没有督办合伙人，无处发送申请','warning');
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
				//准备插入案号

				/*if(!$case['is_query'] && !$case_client_role['client']){
					$this->output->message('申请案号前应当至少添加一个客户','warning');
				}else{*/
				$data=array(
					'num'=>$this->cases->getNum($case,$case_client_role),
					'type_lock'=>1
				);

				if($this->cases->update($this->cases->id,$data)){
					echo json_encode(array('message'=>$this->output->message,'url'=>'/cases/edit/'.$this->cases->id));
				}
				//}
			}

			elseif(isset($case_client_role['client']) && !post('cases/filed')){
				//根据案件类别和客户、相对方更新案名
				$case_client_role['client_name']='<a href="javascript:showWindow(\'client/edit/'.$case_client_role['client'].'\')">'.$case_client_role['client_name'].'</a>';

				$case_client_role['opposite_name']='<a href="javascript:showWindow(\'client/edit/'.$case_client_role['opposite'].'\')">'.$case_client_role['opposite_name'].'</a>';

				post('cases/name',$this->cases->getName($case_client_role,post('cases/is_query'),post('cases/classification'),post('cases/type'),post('cases/name_extra')));

			}

			elseif($submit=='case'){
				if(!post('cases/num')){
					$this->output->message('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
				}
				if(post('cases/classification')!='法律顾问' && !post('cases/is_query') && !post('cases/focus')){
					$this->output->message('请填写案件争议焦点','warning');
				}
			}

		}catch(Exception $e){
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