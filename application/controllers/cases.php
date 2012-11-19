<?php
class Cases extends SS_controller{
	function __construct(){
		parent::__construct();
		$this->default_method='lists';
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
		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));

		$field=array(
			'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','td'=>'title="立案时间：{time_contract}"','content'=>'<a href="/cases/edit/{id}">{num}</a>'),
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
		$table=$this->table->setFields($field)
			->setData($this->cases->getList($para))
			->generate();
		$this->load->addViewData('list',$table);
		$this->load->view('list');
	}
	
	function add(){
		$this->edit();
	}
	
	function edit($id=NULL){
		$this->load->model('staff_model','staff');
		$this->load->model('client_model','client');
		$this->load->model('schedule_model','schedule');

		$this->getPostData($id,function($CI){
			post('cases/time_contract',$CI->config->item('date'));
			post('cases/time_end',date('Y-m-d',$CI->config->item('timestamp')+100*86400));
			//默认签约时间和结案时间
		
			post('case_client_extra/show_add_form',true);
			post('case_lawyer_extra/show_add_form',true);
		});

		$case_role=$this->cases->getRoles(post('cases/id'));
		
		$responsible_partner=$this->cases->getPartner($case_role);
		//获得本案督办合伙人的id
		
		$lawyers=$this->cases->getLawyers($case_role);
		//获得本案办案人员的id
		
		$my_roles=$this->cases->getMyRoles($case_role);
		//本人的本案职位
		
		$this->load->addViewArrayData(compact('case_role','responsible_partner','lawyers','my_roles'));
		
		$submitable=false;
		
		if($this->input->post('submit')){
			$_SESSION['cases']['post']=array_replace_recursive($_SESSION['cases']['post'],$this->input->post());
			$submitable=true;
			
			if(is_posted('submit/case_client')){
				if(!is_posted('case_client_extra/character')){//[单位]不打钩则删除session对应变量
					unset($_SESSION[CONTROLLER]['post']['case_client_extra']['character']);
				}
				$client_check=$this->client->check(post('case_client_extra/name'),'array');
				if($client_check==-1){//如果case_client添加的客户不存在，则先添加客户
					$new_client=array(
						'name'=>post('case_client_extra/name'),
						'character'=>post('case_client_extra/character')=='单位'?'单位':'自然人',
						'classification'=>post('case_client_extra/classification'),
						'type'=>post('case_client_extra/type')
					);
				
					if(post('case_client_extra/classification')=='客户'){//客户必须输入来源
						$client_source=$this->client->setSource(post('case_client_extra/source_type'),post('case_client_extra/source_detail'));
						
						$staff_check=$this->staff->check(post('case_client_extra/source_lawyer_name'),'id',false);
						
						if($staff_check<0){
							showMessage('请输入正确的来源律师','warning');
						}
			
						if($staff_check>0 && $client_source>0){
							$new_client['source_lawyer']=$staff_check;
							$new_client['source']=$client_source;
							if(!post('cases/source')){
								post('cases/source',$client_source);
							}
						}else{
							$submitable=false;
						}
					}else{//非客户必须输入工作单位
						if(post('case_client_extra/work_for')){
							$new_client['work_for']=post('case_client_extra/work_for');
						}else{
							showMessage('请输入工作单位','warning');
							$submitable=false;
						}
					}
					
					if($submitable && $new_client_id=$this->client->add($new_client)){
						post('case_client/client',$new_client_id);
			
						$this->client->addContact_phone_email(post('case_client/client'),post('case_client_extra/phone'),post('case_client_extra/email'));
			
						showMessage(
							'<a href="javascript:showWindow(\''.
							(post('case_client_extra/classification')=='客户'?'client':'contact').
							'?edit='.post('case_client/client').'\')" target="_blank">新'.
							post('case_client_extra/classification').' '.post('case_client_extra/name').
							' 已经添加，点击编辑详细信息</a>',
						'notice');
					}
		
				}elseif($client_check>0){
					post('case_client/client',$client_check['id']);
					if(!post('cases/source')){
						post('cases/source',$client_check['source']);
					}
					showMessage('系统中已经存在 '.$client_check['name'].'，已自动识别');
				}else{
					//除了不存在意外的其他错误，如关键字多个匹配
					$submitable=false;
				}
		
				if($submitable && $this->cases->addClient(post('cases/id'),post('case_client/client'),post('case_client/role'))){
					unset($_SESSION['cases']['post']['case_client']);
					unset($_SESSION['cases']['post']['case_client_extra']);
					post('case_client_extra/show_add_form',false);
				}
		
				post('case_client_extra/show_add_form',true);//无论插入是否成功，刷新后继续显示添加客户表单
			}
			
			if(is_posted('submit/case_lawyer')){
				if(post('case_lawyer/lawyer',$this->staff->check(post('case_lawyer_extra/lawyer_name'),'id'))<0){
					//查找职员并保存至session，捕获错误
					$submitable=false;
		
				}elseif(post('case_lawyer/role')=='实际贡献' && !(in_array('督办合伙人',$my_roles) || in_array('主办律师',$my_roles))){
					//禁止非主办律师/合伙人分配实际贡献
					showMessage('你没有权限分配实际贡献');
					$submitable=false;
					
				}elseif(post('case_lawyer/contribute',$this->cases->lawyerRoleCheck(post('cases/id'),post('case_lawyer/role'),post('case_lawyer_extra/actual_contribute')))===false){
					//检查并保存本条case_lawyer的contribute，若不可添加则返回false并终止过程
					$submitable=false;
					
				}else{
					post('case_lawyer/hourly_fee',(int)post('case_lawyer/hourly_fee'));
					
					if(!$responsible_partner && post('case_lawyer/role')!='督办合伙人'){
						//第一次插入督办合伙人后不显示警告，否则如果不存在督办合伙人则显示警告
						showMessage('未设置督办合伙人','warning');
						$submitable=false;
					}
					if($submitable && $this->cases->addLawyer(post('cases/id'),post('case_lawyer'))){
						unset($_SESSION['cases']['post']['case_lawyer']);
						unset($_SESSION['cases']['post']['case_lawyer_extra']);
						if(post('cases/is_reviewed') && post('case_lawyer/role')!='实际贡献' && !in_array('督办合伙人',$my_roles)){
							post('cases/is_reviewed',0);
							showMessage('案件关键信息已经更改，需要重新审核');
						}
					}
				}
				post('case_lawyer_extra/show_add_form',true);//无论是否插入成功，刷新后继续显示添加律师表单
				
				$this->cases->calcContribute(post('cases/id'));
			}
			
			if(is_posted('submit/case_fee')){
				
				post('case_fee/pay_time',strtotime(post('case_fee_extra/pay_time')));
		
				if(!post('case_fee/fee')){
					showMessage('请预估收费金额','warning');
		
				}elseif(!post('case_fee/pay_time')){
					showMessage('请预估收费时间','warning');
		
				}else{		
					if($this->cases->addFee(post('cases/id'),post('case_fee'))){
						unset($_SESSION['cases']['post']['case_fee']);
						if(post('cases/is_reviewed')){
							post('cases/is_reviewed',0);
							showMessage('案件关键信息已经更改，需要重新审核');
						}
					}
				}
			}
			
			if(is_posted('submit/case_fee_review') && is_logged('finance')){
				//财务审核
				$condition = db_implode(post('case_fee_check'), $glue = ' OR ','id','=',"'","'", '`','key');
				$condition_account=db_implode(post('case_fee_check'), $glue = ' OR ','case_fee','=',"'","'", '`','key');
				
				if($this->db->update('case_fee',array('reviewed'=>1),$condition)){
					showMessage('已完成案件收费到账审核');
				}
			}
			
			if(is_posted('submit/case_fee_timing')){
				
				if(!is_posted('cases/timing_fee')){
					post('cases/timing_fee',0);
					$q_delete_case_fee_timing="DELETE FROM case_fee_timing WHERE `case`='".post('cases/id')."'";
					$q_delete_case_fee_timing_type="DELETE FROM case_fee WHERE `case`='".post('cases/id')."' AND type='计时预付'";
					db_query($q_delete_case_fee_timing);
					db_query($q_delete_case_fee_timing_type);
				}
		
				post('case_fee_timing',array_trim(post('case_fee_timing')));
				
				post('case_fee_timing/case',post('cases/id'));
		
				post('case_fee_timing/time_start',strtotime(post('case_fee_timing_extra/time_start')));//imperfect 2012/5/23 uicestone
				
				post('case_fee_timing/uid',$_SESSION['id']);
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
		
			if(is_posted('submit/case_fee_misc')){
				
				post('case_fee_misc/case',post('cases/id'));
				
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
		
			if(is_posted('submit/case_document')){
				$this->load->model('document_model','document');
				if(post('case_document/doctype')=='其他' && !post('case_document/doctype_other')){
					showMessage('文件类别选择“其他”，则必须填写具体类别','warning');
		
				}elseif($_FILES["file"]["error"]>0){
					showMessage('文件上传错误:'.$_FILES["file"]["error"],'warning');
		
				}else{
					$storePath=iconv("utf-8","gbk",$this->config->item('case_document_path')."/".$_FILES["file"]["name"]);//存储路径转码
					
					move_uploaded_file($_FILES['file']['tmp_name'], $storePath);
				
					post('case_document/name',$_FILES["file"]["name"]);
					post('case_document/type',$this->document->getExtension(post('case_document/name')));
					post('case_document/size',$_FILES["file"]['size']);
					
					$_SESSION['case']['post']['case_document']['id']=$this->cases->addDocument(post('cases/id'),post('case_document'));
		
					rename(iconv("utf-8","gbk",$this->config->item('case_document_path')."/".$_FILES["file"]["name"]),iconv("utf-8","gbk",$this->config->item('case_document_path')."/".post('case_document/id')));
				}
				
				unset($_SESSION['cases']['post']['case_document']);
			}
		
			if(is_posted('submit/file_document_list')){
				$this->require_export=FALSE;
				model('document');
				$document_catalog=$this->cases->getDocumentCatalog(post('cases/id'),post('case_document_check'));
				require 'view/case_document_catalog.php';
			}
			
			if(is_posted('submit/case_client_delete')){
		
				$condition = db_implode(post('case_client_check'), $glue = ' OR ','id','=',"'","'", '`','key');
		
				$q="DELETE FROM case_client WHERE (".$condition.")";
		
				db_query($q);
		
				if(post('cases/is_reviewed')){
					post('cases/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
		
			if(is_posted('submit/case_lawyer_delete')){
		
				$condition = db_implode(post('case_lawyer_check'), $glue = ' OR ','id','=',"'","'", '`','key');
		
				$q="DELETE FROM case_lawyer WHERE (".$condition.")";
		
				db_query($q);
				
				$this->cases->calcContribute(post('cases/id'));
		
				if(post('cases/is_reviewed')){
					post('cases/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
		
			if(is_posted('submit/case_fee_delete')){
		
				$condition = db_implode(post('case_fee_check'), $glue = ' OR ','id','=',"'","'", '`','key');
				
				db_delete('case_fee',$condition);
		
				if(post('cases/is_reviewed')){
					post('cases/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
		
			if(is_posted('submit/new_case')){
				post('cases/is_query',0);
				post('cases/filed',0);
				post('cases/num','');
				post('cases/time_contract',$this->config->item('date'));
				post('cases/time_end',date('Y-m-d',$this->config->item('timestamp')+100*86400));
				//默认签约时间和结案时间
		
				showMessage('已立案，请立即获得案号');
			}
			
			if(post('cases/is_query') && is_posted('submit/file')){
				post('cases/filed',1);
				showMessage('咨询案件已归档');
			}
			
			if(is_posted('submit/send_message')){
				showMessage('本案已被退回');
				case_reviewMessage('被退回审核',$lawyers);
			}
		
			if(is_posted('submit/review')){
				post('cases/is_reviewed',1);
				showMessage('本案已经审核通过');
				$this->cases->reviewMessage('通过审核',$lawyers);
			}
			
			if(is_posted('submit/apply_lock')){
				//申请锁定，发送一条消息给督办合伙人
				if($responsible_partner){
					$apply_lock_message=$_SESSION['username'].'申请锁定'.strip_tags(post('cases/name')).'一案，[url=http://sys.lawyerstars.com/cases/edit/'.post('cases/id').']点此进入[/url]';
					sendMessage($responsible_partner,$apply_lock_message,'caseLockApplication');//imperfect
					showMessage('锁定请求已经发送至本案督办合伙人');
				}else{
					showMessage('本案没有督办合伙人，无处发送申请','warning');
				}
			}
			
			if(is_posted('submit/lock_type')){
				post('cases/type_lock',1);
			}
		
			if(is_posted('submit/lock_client')){
				post('cases/client_lock',1);
			}
		
			if(is_posted('submit/lock_lawyer')){
				post('cases/lawyer_lock',1);
			}
		
			if(is_posted('submit/lock_fee')){
				post('cases/fee_lock',1);
			}
			
			if(is_posted('submit/unlock_client')){
				post('cases/client_lock',0);
			}
			
			if(is_posted('submit/unlock_lawyer')){
				post('cases/lawyer_lock',0);
			}
			
			if(is_posted('submit/unlock_fee')){
				post('cases/fee_lock',0);
			}
			
			if(is_posted('submit/apply_file')){
				post('cases/time_end',$this->config->item('date'));
				post('cases/apply_file',1);
				showMessage('归档申请已接受');
			}
			
			if(is_posted('submit/review_finance')){
				post('cases/finance_review',1);
				showMessage('结案财务状况已经审核');
			}
		
			if(is_posted('submit/review_info')){
				post('cases/info_review',1);
				showMessage('案件信息已经审核');
			}
			
			if(is_posted('submit/review_manager')){
				post('cases/time_end',$this->config->item('date'));
				post('cases/manager_review',1);
				showMessage('案件已经审核，已正式归档');
			}
			
			if(!post('cases/is_query') && is_posted('submit/file')){
				db_insert('file_status',array('case'=>post('cases/id'),'status'=>'在档','time'=>$this->config->item('timestamp')));
				post('cases/filed',1);
				showMessage('案件实体归档完成');
			}
			
			$case_client_role = $this->cases->getClientRole(post('cases/id'));
			
			if(is_posted('submit/apply_case_num') && !post('cases/num')){
				//准备插入案号
				
				if(!$case['is_query'] && !$case_client_role['client']){
					showMessage('申请案号前应当至少添加一个客户','warning');
				}else{
					post('cases/num',$this->cases->getNum(post('cases'),$case_client_role));
					post('cases/type_lock',1);
				}
			}
		
			if(isset($case_client_role['client']) && !post('cases/filed')){
				//根据案件类别和客户、相对方更新案名
				//TODO 没有填相对方的时候会报错，不过不影响运行
				$case_client_role['client_name']='<a href="javascript:showWindow(\'client/edit/'.$case_client_role['client'].'\')">'.$case_client_role['client_name'].'</a>';
		
				$case_client_role['opposite_name']='<a href="javascript:showWindow(\'client/edit/'.$case_client_role['opposite'].'\')">'.$case_client_role['opposite_name'].'</a>';
				
				post('cases/name',$this->cases->getName($case_client_role,post('cases/classification'),post('cases/type'),post('cases/is_query')));
		
			}
		
			if(post('cases/name_extra')){
				post('cases/name',post('cases/name').' '.post('cases/name_extra'));
			}
			
			if(is_posted('submit/case') && !post('cases/num')){
				$submitable=false;
				showMessage('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
			}
			
			if(is_posted('submit/case') && post('cases/classification')!='法律顾问' && !post('cases/is_query') && !post('cases/focus')){
				$submitable=false;
				showMessage('请填写案件争议焦点','warning');
			}
			
			$this->processSubmit($submitable,NULL,NULL,false,true,false);

		}
		
		if(post('cases/client_lock')){
			post('case_client_extra/classification','联系人');
		}		

		//计算本案有效日志总时间
		$this->load->view_data['schedule_time']=$this->schedule->calculateTime(post('cases/id'));
		
		$this->load->view_data['case_status']=$this->cases->getStatusById(post('cases/id'));
		
		$this->load->view_data['case_type_array']=db_enumArray('case','stage');
		
		if(post('cases/is_query')){
			$this->load->view_data['case_lawyer_role_array']=array('督办合伙人','接洽律师','接洽律师（次要）','律师助理');
		}else{
			$this->load->view_data['case_lawyer_role_array']=db_enumArray('case_lawyer','role');
		}
		
		//案下客户列表
		$fields_client=array(
			'client_name'=>array('title'=>'名称','eval'=>true,'content'=>"
				\$return='';
				if(!post('cases/client_lock')){
					\$return.='<input type=\"checkbox\" name=\"case_client_check[{id}]\" />';
				}
				\$return.='<a href=\"javascript:showWindow(\''.('{classification}'=='客户'?'client':'contact').'/edit/{client}\')\">{client_name}</a>';
				return \$return;
			",'orderby'=>false),
			'contact_name'=>array('title'=>'联系人','eval'=>true,'content'=>"
				return '<a href=\"javascript:showWindow(\''.('{contact_classification}'=='客户'?'client':'contact').'?edit={contact}\')\">{contact_name}</a>';
			",'orderby'=>false),
			'phone'=>array('title'=>'电话','td'=>'class="ellipsis" title="{phone}"'),
			'email'=>array('title'=>'电邮','wrap'=>array('mark'=>'a','href'=>'mailto:{email}','title'=>'{email}','target'=>'_blank'),'td'=>'class="ellipsis"'),
			'role'=>array('title'=>'本案地位','orderby'=>false),
			'classification'=>array('title'=>'类型','td_title'=>'width="60px"','orderby'=>false)
		);
		if(!post('cases/client/lock')){
			//客户锁定时不显示删除按钮
			$fields_client['client_name']['title']='<input type="submit" name="submit[case_client_delete]" value="删" />'.$fields_client['client_name']['title'];
		}
		$this->load->view_data['case_client_table']=$this->table->setFields($fields_client)
			->generate($this->cases->getClientList(post('cases/id')));
		
		post('case_client_extra/classification','客户');
		
		if(post('cases/is_query')){
			post('case_client_extra/type','潜在客户');
		}else{
			post('case_client_extra/type','成交客户');//让案下客户添加默认为成交客户
		}
		
		//案下职员列表
		$fields_staff=array(
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
		if(post('cases/timing_fee')){
			$fields_staff['hourly_fee']=array('title'=>'计时收费小时费率','td'=>'class="editable" id="{id}"','orderby'=>false);
		}
		if(!post('cases/staff_lock')){
			//律师锁定时不显示删除按钮
			$fields_staff['lawyer_name']['title']='<input type="submit" name="submit[case_lawyer_delete]" value="删" />'.$fields_staff['lawyer_name']['title'];
			$fields_staff['lawyer_name']['content']='<input type="checkbox" name="case_lawyer_check[{id}]">'.$fields_staff['lawyer_name']['content'];
		}
		$this->load->view_data['case_staff_table']=$this->cases->table->setFields($fields_staff)
				->generate($this->cases->getStaffList(post('cases/id')));
		
		//案下收费列表
		$fields_fee=array(
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
		if(!post('cases/fee_lock')){
			$fields_fee['type']['title']='<input type="submit" name="submit[case_fee_delete]" value="删" />'.$fields_fee['type']['title'];
		}
		if(!post('cases/fee_lock') || is_logged('finance')){
			$fields_fee['type']['content']='<input type="checkbox" name="case_fee_check[{id}]" >'.$fields_fee['type']['content'];
		}
		$this->load->view_data['case_fee_table']=$this->cases->table->setFields($fields_fee)
				->generate($this->cases->getFeeList(post('cases/id')));
		
		if(post('case_fee/pay_time')){
			post('case_fee_extra/pay_time',date('Y-m-d',post('case_fee/pay_time')));
		}
		
		if(post('cases/timing_fee')){
			$this->load->view_data['case_fee_timing_string']=$this->cases->getTimingFeeString(post('cases/id'));
			if(post('case_timing_fee/time_start')){
				post('case_timing_fee_extra/time_start',date('Y-m-d',post('case_timing_fee/time_start')));
			}
		}
		
		//办案费列表
		$fields_fee_misc=array(
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
		if(!post('fee_lock')){
			$fields_fee_misc['receiver']['title']='<input type="submit" name="submit[case_fee_delete]" value="删" />'.$fields_fee_misc['receiver']['title'];
			$fields_fee_misc['receiver']['content']='<input type="checkbox" name="case_fee_check[{id}]" />{receiver}';
		}
		$this->load->view_data['case_fee_misc_table']=$this->table->setFields($fields_fee_misc)
				->generate($this->cases->getFeeMiscList(post('cases/id')));

		if(post('case_fee_misc/pay_time')){
			post('case_fee_misc_extra/pay_time',date('Y-m-d',post('case_fee_misc/pay_time')));
		}
		
		//文件列表
		$fields_document=array(
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
		if(post('cases/apply_file')){
			array_splice($fields_document,0,0,array(
				'id'=>array('title'=>'','td_title'=>'width="37px"','content'=>'<input type="checkbox" name="case_document_check[{id}]" checked="checked" />')
			));
		}
		$this->load->view_data['case_document_table']=$this->table->setFields($fields_document)
				->generate($this->cases->getDocumentList(post('cases/id')));
		
		//日程列表
		$fields_schedule=array(
			'name'=>array('title'=>'标题','td_title'=>'width="150px"','wrap'=>array('mark'=>'a','href'=>'javascript:showWindow(\'schedule/edit/{id}\')'),'orderby'=>false),
			'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
				return date('m-d H:i',{time_start});
			",'orderby'=>false),
			'username'=>array('title'=>'填写人','td_title'=>'width="90px"','orderby'=>false)
		);
		$this->load->view_data['case_schedule_table']=$this->table->setFields($fields_schedule)
				->generate($this->cases->getScheduleList(post('cases/id')));
		
		//计划列表
		$fields_plan=array(
			'name'=>array('title'=>'标题','td_title'=>'width="150px"','wrap'=>array('mark'=>'a','href'=>'javascript:showWindow(\'schedule/edit/{id}\')'),'orderby'=>false),
			'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
				return date('m-d H:i',{time_start});
			",'orderby'=>false),
			'username'=>array('title'=>'填写人','td_title'=>'width="90px"','orderby'=>false)
		);
		$this->load->view_data['case_plan_table']=$this->table->setFields($fields_plan)
				->generate($this->cases->getPlanList(post('cases/id')));
		
		$this->load->view('cases/edit',$this->load->view_data);
		
		$this->load->main_view_loaded=TRUE;
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

	function reviewList(){
		$q="
			SELECT
				case.id,case.name,case.num,case.stage,case.stage,
				lawyers.lawyers
			FROM 
				`case`
			
				LEFT JOIN
				(
					SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
					FROM case_lawyer,staff 
					WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
					GROUP BY case_lawyer.`case`
				)lawyers
				ON `case`.id=lawyers.`case`
				WHERE case.display=1 AND case.id>=20 AND case.lawyer_lock=0 AND case.is_reviewed=0
		";
		
		$search_bar=$this->processSearch($q,array('case_num_grouped.num'=>'案号','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));
		
		$this->processOrderby($q,'case.time_contract','DESC',array('case.name','lawyers'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','content'=>'<a href="/cases/edit/{id}">{num}</a>'),
			'name'=>array('title'=>'案名','content'=>'{name}'),
			'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"')
		);
		
		$menu=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$this->input->server('REQUEST_URI');
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->load->view_data+=compact('table','menu');
		
		$this->load->view('lists',$this->load->view_data);
		
		require 'view/case_list_sidebar.htm';
	}
	
	function write(){
		if($this->input->get('case_fee_condition')){
			$id=intval($this->input->post('id'));
			$value=$this->input->post('value');
		
			if($case_feeConditionPrepend=case_feeConditionPrepend($id,$value)){
				echo json_encode($case_feeConditionPrepend);
			}
		}
	}
}
?>