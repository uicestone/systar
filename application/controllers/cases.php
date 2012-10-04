<?php
class Cases extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$q="
			SELECT
				case.id,case.name,case.num,case.stage,case.time_contract,
				case.is_reviewed,case.apply_file,case.is_query,
				case.type_lock*case.client_lock*case.lawyer_lock*case.fee_lock AS locked,
				case.finance_review,case.info_review,case.manager_review,case.filed,
				contribute_allocate.contribute_sum,
				uncollected.uncollected,
				schedule_grouped.id AS schedule,schedule_grouped.name AS schedule_name,schedule_grouped.time_start,schedule_grouped.username AS schedule_username,
				plan_grouped.id AS plan,plan_grouped.name AS plan_name,FROM_UNIXTIME(plan_grouped.time_start,'%m-%d') AS plan_time,plan_grouped.username AS plan_username,
				lawyers.lawyers
			FROM 
				`case`
			
				LEFT JOIN
				(
					SELECT * FROM(
						SELECT * FROM `schedule` WHERE completed=1 AND display=1 ORDER BY time_start DESC LIMIT 1000
					)schedule_id_desc 
					GROUP BY `case`
				)schedule_grouped
				ON `case`.id = schedule_grouped.`case`
				
				LEFT JOIN
				(
					SELECT * FROM(
						SELECT * FROM `schedule` WHERE completed=0 AND display=1 AND time_start>'{$_G['timestamp']}' ORDER BY time_start LIMIT 1000
					)schedule_id_asc 
					GROUP BY `case`
				)plan_grouped
				ON `case`.id = plan_grouped.`case`
				
				LEFT JOIN
				(
					SELECT `case`,GROUP_CONCAT(staff.name) AS lawyers
					FROM case_lawyer,staff 
					WHERE case_lawyer.lawyer=staff.id AND case_lawyer.role='主办律师'
					GROUP BY case_lawyer.`case`
				)lawyers
				ON `case`.id=lawyers.`case`
				
				LEFT JOIN 
				(
					SELECT `case`,SUM(contribute) AS contribute_sum
					FROM case_lawyer
					GROUP BY `case`
				)contribute_allocate
				ON `case`.id=contribute_allocate.case
				
				LEFT JOIN
				(
					SELECT `case`,IF(amount_sum IS NULL,fee_sum,fee_sum-amount_sum) AS uncollected FROM
					(
						SELECT `case`,SUM(fee) AS fee_sum FROM case_fee WHERE type<>'办案费' AND reviewed=0 GROUP BY `case`
					)case_fee_grouped
					INNER JOIN
					(
						SELECT `case`, SUM(amount) AS amount_sum FROM account GROUP BY `case`
					)account_grouped
					USING (`case`)
				)uncollected
				ON case.id=uncollected.case
				
			WHERE case.company='{$_G['company']}' AND case.display=1 AND is_query=0 AND case.filed=0 AND case.id>=20
		";
		
		//此query过慢，用其简化版计算总行数
		$q_rows="
			SELECT
				COUNT(id)
			FROM 
				`case`
			WHERE case.company='{$_G['company']}' AND case.display=1 AND is_query=0 AND case.filed=0 AND case.id>=20
		";
		
		$condition='';
		
		if(got('host')){
			$condition.="AND case.apply_file=0 AND case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role='主办律师')";
		
		}elseif(got('consultant')){
			$condition.="AND case.apply_file=0 AND classification='法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."') OR case.uid='".$_SESSION['id']."')";
		
		}elseif(got('etc')){
			$condition.="AND case.apply_file=0 AND classification<>'法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role<>'主办律师') OR case.uid='".$_SESSION['id']."')";
			
		}elseif(got('file')){
			$condition.="AND case.apply_file=1 AND classification<>'法律顾问' AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role<>'主办律师') OR case.uid='".$_SESSION['id']."')";
			
		}elseif(!is_logged('developer')){
			$condition.="AND (case.id IN (SELECT `case` FROM case_lawyer WHERE lawyer='".$_SESSION['id']."' AND role IN ('接洽律师','接洽律师（次要）','主办律师','协办律师','律师助理','督办合伙人')) OR case.uid='".$_SESSION['id']."')";
		}
		
		$search_bar=$this->processSearch($condition,array('case.num'=>'案号','case.type'=>'类别','case.name'=>'名称','lawyers.lawyers'=>'主办律师'));
		
		processOrderby($condition,'time_contract','DESC',array('case.name','lawyers'));
		
		$q.=$condition;
		$q_rows.=$condition;
		
		$listLocator=$this->processMultiPage($q,$q_rows);
		
		$field=array(
			'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','td'=>'title="立案时间：{time_contract}"','content'=>'<a href="case?edit={id}">{num}</a>'),
			'name'=>array('title'=>'案名','content'=>'{name}'),
			'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
			'schedule_grouped.time_start'=>array('title'=>'最新日志','eval'=>true,'content'=>"
				return '<a href=\"javascript:showWindow(\'schedule?add&case={id}\')\">+</a> <a href=\"schedule?list&case={id}\" title=\"{schedule_name}\">'.str_getSummary('{schedule_name}').'</a>';
			"),
			'plan_grouped.time_start'=>array('title'=>'最近提醒','eval'=>true,'content'=>"
				return '<a href=\"javascript:showWindow(\'schedule?add&case={id}&completed=0\')\">+</a> {plan_time} <a href=\"schedule?list&plan&case={id}\" title=\"{plan_name}\">'.str_getSummary('{plan_name}').'</a>';
			"),
			'is_reviewed'=>array('title'=>'状态','td_title'=>'width="75px"','eval'=>true,'content'=>"
				return case_getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			",'orderby'=>false)
		);
		
		$submitBar=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$submitBar);
	}
	
	function add(){
		$this->edit();
	}
	
	function edit($id=NULL){
		$this->load->model('staff_model','staff');
		$this->load->model('client_model','client');
		$this->load->model('schedule_model','schedule');
		
		getPostData(function(){
			post('case/time_contract',$_G['date']);
			post('case/time_end',date('Y-m-d',$_G['timestamp']+100*86400));
			//默认签约时间和结案时间
		
			post('case_client_extra/show_add_form',true);
			post('case_lawyer_extra/show_add_form',true);
		});
		
		$case_role=case_getRoles(post('case/id'));
		
		$responsible_partner=case_getPartner($case_role);
		//获得本案督办合伙人的id
		
		$lawyers=case_getLawyers($case_role);
		//获得本案办案人员的id
		
		$my_roles=case_getMyRoles($case_role);
		//本人的本案职位
		
		$submitable=false;
		
		if(is_posted('submit')){
			$_SESSION['case']['post']=array_replace_recursive($_SESSION['case']['post'],$_POST);
			$submitable=true;
			
			if(is_posted('submit/case_client')){
				if(!is_posted('case_client_extra/character')){//[单位]不打钩则删除session对应变量
					unset($_SESSION[IN_UICE]['post']['case_client_extra']['character']);
				}
				$client_check=client_check(post('case_client_extra/name'),'array');
				if($client_check==-1){//如果case_client添加的客户不存在，则先添加客户
					$new_client=array(
						'name'=>post('case_client_extra/name'),
						'character'=>post('case_client_extra/character')=='单位'?'单位':'自然人',
						'classification'=>post('case_client_extra/classification'),
						'type'=>post('case_client_extra/type')
					);
				
					if(post('case_client_extra/classification')=='客户'){//客户必须输入来源
						$client_source=client_setSource(post('case_client_extra/source_type'),post('case_client_extra/source_detail'));
						
						$staff_check=staff_check(post('case_client_extra/source_lawyer_name'),'id',false);
						
						if($staff_check<0){
							showMessage('请输入正确的来源律师','warning');
						}
			
						if($staff_check>0 && $client_source>0){
							$new_client['source_lawyer']=$staff_check;
							$new_client['source']=$client_source;
							if(!post('case/source')){
								post('case/source',$client_source);
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
					
					if($submitable && $new_client_id=client_add($new_client)){
						post('case_client/client',$new_client_id);
			
						client_addContact_phone_email(post('case_client/client'),post('case_client_extra/phone'),post('case_client_extra/email'));
			
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
					if(!post('case/source')){
						post('case/source',$client_check['source']);
					}
					showMessage('系统中已经存在 '.$client_check['name'].'，已自动识别');
				}else{
					//除了不存在意外的其他错误，如关键字多个匹配
					$submitable=false;
				}
		
				if($submitable && case_addClient(post('case/id'),post('case_client/client'),post('case_client/role'))){
					unset($_SESSION['case']['post']['case_client']);
					unset($_SESSION['case']['post']['case_client_extra']);
					post('case_client_extra/show_add_form',false);
				}
		
				post('case_client_extra/show_add_form',true);//无论插入是否成功，刷新后继续显示添加客户表单
			}
			
			if(is_posted('submit/case_lawyer')){
				if(post('case_lawyer/lawyer',staff_check(post('case_lawyer_extra/lawyer_name'),'id'))<0){
					//查找职员并保存至session，捕获错误
					$submitable=false;
		
				}elseif(post('case_lawyer/role')=='实际贡献' && !(in_array('督办合伙人',$my_roles) || in_array('主办律师',$my_roles))){
					//禁止非主办律师/合伙人分配实际贡献
					showMessage('你没有权限分配实际贡献');
					$submitable=false;
					
				}elseif(post('case_lawyer/contribute',case_lawyerRoleCheck(post('case/id'),post('case_lawyer/role'),post('case_lawyer_extra/actual_contribute')))===false){
					//检查并保存本条case_lawyer的contribute，若不可添加则返回false并终止过程
					$submitable=false;
					
				}else{
					post('case_lawyer/hourly_fee',(int)post('case_lawyer/hourly_fee'));
					
					if(!$responsible_partner && post('case_lawyer/role')!='督办合伙人'){
						//第一次插入督办合伙人后不显示警告，否则如果不存在督办合伙人则显示警告
						showMessage('未设置督办合伙人','warning');
						$submitable=false;
					}
					if($submitable && case_addLawyer(post('case/id'),post('case_lawyer'))){
						unset($_SESSION['case']['post']['case_lawyer']);
						unset($_SESSION['case']['post']['case_lawyer_extra']);
						if(post('case/is_reviewed') && post('case_lawyer/role')!='实际贡献' && !in_array('督办合伙人',$my_roles)){
							post('case/is_reviewed',0);
							showMessage('案件关键信息已经更改，需要重新审核');
						}
					}
				}
				post('case_lawyer_extra/show_add_form',true);//无论是否插入成功，刷新后继续显示添加律师表单
				
				case_calcContribute(post('case/id'));
			}
			
			if(is_posted('submit/case_fee')){
				
				post('case_fee/pay_time',strtotime(post('case_fee/pay_time')));
		
				if(!post('case_fee/fee')){
					showMessage('请预估收费金额','warning');
		
				}elseif(!post('case_fee/pay_time')){
					showMessage('请预估收费时间','warning');
		
				}else{		
					if(case_addFee(post('case/id'),post('case_fee'))){
						unset($_SESSION['case']['post']['case_fee']);
						if(post('case/is_reviewed')){
							post('case/is_reviewed',0);
							showMessage('案件关键信息已经更改，需要重新审核');
						}
					}
				}
			}
			
			if(is_posted('submit/case_fee_review') && is_logged('finance')){
				//财务审核
				$condition = db_implode(post('case_fee_check'), $glue = ' OR ','id','=',"'","'", '`','key');
				$condition_account=db_implode(post('case_fee_check'), $glue = ' OR ','case_fee','=',"'","'", '`','key');
				
				if(db_update('case_fee',array('reviewed'=>1),$condition)
					&& db_update('account',array('reviewed'=>1),$condition_account)
				){
					showMessage('已完成案件收费到账审核');
				}
			}
			
			if(is_posted('submit/case_fee_timing')){
				
				if(!is_posted('case/timing_fee')){
					post('case/timing_fee',0);
					$q_delete_case_fee_timing="DELETE FROM case_fee_timing WHERE `case`='".post('case/id')."'";
					$q_delete_case_fee_timing_type="DELETE FROM case_fee WHERE `case`='".post('case/id')."' AND type='计时预付'";
					db_query($q_delete_case_fee_timing);
					db_query($q_delete_case_fee_timing_type);
				}
		
				post('case_fee_timing',array_trim(post('case_fee_timing')));
				
				post('case_fee_timing/case',post('case/id'));
		
				post('case_fee_timing/time_start',strtotime(post('case_fee_timing/time_start')));//imperfect 2012/5/23 uicestone
				
				post('case_fee_timing/timing_start');
				
				post('case_fee_timing/uid',$_SESSION['id']);
				post('case_fee_timing/time',$_G['timestamp']);
					
				if(
					post('case/timing_fee') && 
					(!post('case_fee_timing/time_start') || 
					post('case_fee_timing/included_hours')=='')
				){
					showMessage('账单起始日或包含小时数未填','warning');
		
				}else{
					if(db_insert('case_fee_timing',post('case_fee_timing'),true,true)){
						unset($_SESSION['case']['post']['case_fee_timing']);
					}
				}
			}
		
			if(is_posted('submit/case_fee_misc')){
				
				post('case_fee_misc/case',post('case/id'));
				
				post('case_fee_misc/type','办案费');
				
				post('case_fee_misc/pay_time',strtotime(post('case_fee_misc/pay_time')));
				
				if(!post('case_fee_misc/fee')){
					showMessage('请填写办案费约定金额','warning');
		
				}elseif(!post('case_fee_misc/pay_time')){
					showMessage('请填写收费时间','warning');
		
				}else{		
					if(db_insert('case_fee',post('case_fee_misc'))){
						unset($_SESSION['case']['post']['case_fee_misc']);
					}
				}
			}
		
			if(is_posted('submit/case_document')){
				model('document');
				if(post('case_document/doctype')=='其他' && !post('case_document/doctype_other')){
					showMessage('文件类别选择“其他”，则必须填写具体类别','warning');
		
				}elseif($_FILES["file"]["error"]>0){
					showMessage('文件上传错误:'.$_FILES["file"]["error"],'warning');
		
				}else{
					$storePath=iconv("utf-8","gbk",$_G['case_document_path']."/".$_FILES["file"]["name"]);//存储路径转码
					
					move_uploaded_file($_FILES['file']['tmp_name'], $storePath);
				
					post('case_document/name',$_FILES["file"]["name"]);
					post('case_document/type',document_getExtension(post('case_document/name')));
					post('case_document/size',$_FILES["file"]['size']);
					
					$_SESSION['case']['post']['case_document']['id']=case_addDocument(post('case/id'),post('case_document'));
		
					rename(iconv("utf-8","gbk",$_G['case_document_path']."/".$_FILES["file"]["name"]),iconv("utf-8","gbk",$_G['case_document_path']."/".post('case_document/id')));
				}
				
				unset($_SESSION['case']['post']['case_document']);
			}
		
			if(is_posted('submit/file_document_list')){
				$_G['require_export']=false;
				model('document');
				$document_catalog=case_getDocumentCatalog(post('case/id'),post('case_document_check'));
				require 'view/case_document_catalog.php';
			}
			
			if(is_posted('submit/case_client_delete')){
		
				$condition = db_implode(post('case_client_check'), $glue = ' OR ','id','=',"'","'", '`','key');
		
				$q="DELETE FROM case_client WHERE (".$condition.")";
		
				db_query($q);
		
				if(post('case/is_reviewed')){
					post('case/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
		
			if(is_posted('submit/case_lawyer_delete')){
		
				$condition = db_implode(post('case_lawyer_check'), $glue = ' OR ','id','=',"'","'", '`','key');
		
				$q="DELETE FROM case_lawyer WHERE (".$condition.")";
		
				db_query($q);
				
				case_calcContribute(post('case/id'));
		
				if(post('case/is_reviewed')){
					post('case/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
		
			if(is_posted('submit/case_fee_delete')){
		
				$condition = db_implode(post('case_fee_check'), $glue = ' OR ','id','=',"'","'", '`','key');
				
				db_delete('case_fee',$condition);
		
				if(post('case/is_reviewed')){
					post('case/is_reviewed',0);
					showMessage('案件关键信息已经更改，需要重新审核');
				}
			}
		
			if(is_posted('submit/new_case')){
				post('case/is_query',0);
				post('case/num','');
				post('case/time_contract',$_G['date']);
				post('case/time_end',date('Y-m-d',$_G['timestamp']+100*86400));
				//默认签约时间和结案时间
		
				showMessage('已立案，请立即获得案号');
			}
			
			if(post('case/is_query') && is_posted('submit/file')){
				post('case/filed',1);
				showMessage('咨询案件已归档');
			}
			
			if(is_posted('submit/send_message')){
				showMessage('本案已被退回');
				case_reviewMessage('被退回审核',$lawyers);
			}
		
			if(is_posted('submit/review')){
				post('case/is_reviewed',1);
				showMessage('本案已经审核通过');
				case_reviewMessage('通过审核',$lawyers);
			}
			
			if(is_posted('submit/apply_lock')){
				//申请锁定，发送一条消息给督办合伙人
				if($responsible_partner){
					$apply_lock_message=$_SESSION['username'].'申请锁定'.strip_tags(post('case/name')).'一案，[url=http://sys.lawyerstars.com/case?edit='.post('case/id').']点此进入[/url]';
					sendMessage($responsible_partner,$apply_lock_message,'caseLockApplication');//imperfect
					showMessage('锁定请求已经发送至本案督办合伙人');
				}else{
					showMessage('本案没有督办合伙人，无处发送申请','warning');
				}
			}
			
			if(is_posted('submit/lock_type')){
				post('case/type_lock',1);
			}
		
			if(is_posted('submit/lock_client')){
				post('case/client_lock',1);
			}
		
			if(is_posted('submit/lock_lawyer')){
				post('case/lawyer_lock',1);
			}
		
			if(is_posted('submit/lock_fee')){
				post('case/fee_lock',1);
			}
			
			if(is_posted('submit/unlock_client')){
				post('case/client_lock',0);
			}
			
			if(is_posted('submit/unlock_lawyer')){
				post('case/lawyer_lock',0);
			}
			
			if(is_posted('submit/unlock_fee')){
				post('case/fee_lock',0);
			}
			
			if(is_posted('submit/apply_file')){
				post('case/time_end',$_G['date']);
				post('case/apply_file',1);
				showMessage('归档申请已接受');
			}
			
			if(is_posted('submit/review_finance')){
				post('case/finance_review',1);
				showMessage('结案财务状况已经审核');
			}
		
			if(is_posted('submit/review_info')){
				post('case/info_review',1);
				showMessage('案件信息已经审核');
			}
			
			if(is_posted('submit/review_manager')){
				post('case/time_end',$_G['date']);
				post('case/manager_review',1);
				showMessage('案件已经审核，已正式归档');
			}
			
			if(!post('case/is_query') && is_posted('submit/file')){
				db_insert('file_status',array('case'=>post('case/id'),'status'=>'在档','time'=>$_G['timestamp']));
				post('case/filed',1);
				showMessage('案件实体归档完成');
			}
			
			$case_client_role = case_getClientRole(post('case/id'));
			
			if(is_posted('submit/apply_case_num') && post('case/num')==''){
				//准备插入案号
				
				post('case/num',case_getNum(post('case'),$case_client_role));
				post('case/type_lock',1);
			}
		
			if(isset($case_client_role['client']) && !post('case/filed')){
				//根据案件类别和客户、相对方更新案名
				//TODO 没有填相对方的时候会报错，不过不影响运行
				$case_client_role['client_name']='<a href="javascript:showWindow(\'client?edit='.$case_client_role['client'].'\')">'.$case_client_role['client_name'].'</a>';
		
				$case_client_role['opposite_name']='<a href="javascript:showWindow(\'client?edit='.$case_client_role['opposite'].'\')">'.$case_client_role['opposite_name'].'</a>';
		
				//更新案名
				if(post('case/classification')=='诉讼' && ($case_client_role['client_role']=='原告' || $case_client_role['client_role']=='申请人') && ($case_client_role['opposite_role']=='被告' || $case_client_role['opposite_role']=='被申请人')){
						post('case/name',$case_client_role['client_name'].' 诉 '.$case_client_role['opposite_name'].'('.post('case/type').')');
						
				}elseif(post('case/classification')=='诉讼' && ($case_client_role['client_role']=='被告' || $case_client_role['client_role']=='被申请人') && ($case_client_role['opposite_role']=='原告' || $case_client_role['opposite_role']=='申请人')){
						post('case/name',$case_client_role['client_name'].' 应诉 '.$case_client_role['opposite_name'].'('.post('case/type').')');
			
				}elseif(post('case/classification')=='诉讼' && $case_client_role['client_role']=='上诉人'){
					post('case/name',$case_client_role['client_name'].' 上诉 '.$case_client_role['opposite_name'].'('.post('case/type').')');
					
				}elseif(post('case/classification')=='诉讼' && $case_client_role['client_role']=='被上诉人'){
					post('case/name',$case_client_role['client_name'].' 应 '.$case_client_role['opposite_name'].' 上诉('.post('case/type').')');
					
				}elseif(post('case/classification')=='诉讼' && $case_client_role['client_role']=='第三人'){
					post('case/name',$case_client_role['client_name'].' 与 '.$case_client_role['opposite_role'].' '.$case_client_role['opposite_name'].'('.post('case/type').')');
					
				}elseif(post('case/classification')=='法律顾问'){
					post('case/name',$case_client_role['client_name'].'(法律顾问)');
					
				}else{
					post('case/name',$case_client_role['client_name'].(post('case/type')?'('.post('case/type').')':''));
			
				}
			}
		
			post('case/name',post('case/name'));
		
			if(post('case/name_extra')){
				post('case/name',post('case/name').' '.post('case/name_extra'));
			}
			
			if(is_posted('submit/case') && !post('case/num')){
				$submitable=false;
				showMessage('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
			}
			
			if(is_posted('submit/case') && post('case/classification')!='法律顾问' && !post('case/is_query') && !post('case/focus')){
				$submitable=false;
				showMessage('请填写案件争议焦点','warning');
			}
			
			processSubmit($submitable,NULL,NULL,false,true,false);
		}
		
		//计算本案有效日志总时间
		$schedule_time=schedule_calculateTime(post('case/id'));
		
		$case_status=case_getStatusById(post('case/id'));
		
		$case_type_array=db_enumArray('case','stage');
		
		if(post('case/is_query')){
			$case_lawyer_role_array=array('督办合伙人','接洽律师','接洽律师（次要）','律师助理');
		}else{
			$case_lawyer_role_array=db_enumArray('case_lawyer','role');
		}
		
		$case_client_table=case_getClientList(post('case/id'),post('case/client_lock'));
		
		if(post('case/is_query')){
			post('case_client_extra/type','潜在客户');
		}else{
			post('case_client_extra/type','成交客户');//让案下客户添加默认为成交客户
		}
		
		$case_staff_table=case_getStaffList(post('case/id'),post('case/lawyer_lock'),post('case/timing_fee'));
		
		$case_fee_table=case_getFeeList(post('case/id'),post('case/fee_lock'));
		
		if(post('case/timing_fee')){
			$case_fee_timing_string=case_getTimingFeeString(post('case/id'));
		}
		
		$case_fee_misc_table=case_getFeeMiscList(post('case/id'),post('case/fee_lock'));
		
		$case_document_table=case_getDocumentList(post('case/id'),post('case/apply_file'));
		
		$case_schedule_table=case_getScheduleList(post('case/id'));
		
		$case_plan_table=case_getPlanList(post('case/id'));
	}

	function documentDownload(){
		$this->load->model('document_model','document');
		
		if(isset($_GET['document']))
			$id=$_GET['document'];
		else
			exit('file id id not defined');
		
		$q_case_document="SELECT * FROM case_document WHERE id='".$id."'";
		$r_case_document=db_query($q_case_document);
		$case_document=mysql_fetch_array($r_case_document);
		
		//适应各浏览器的文件输出
		$ua = $_SERVER["HTTP_USER_AGENT"];
		
		$filename = $case_document['name'];
		$encoded_filename = urlencode($filename);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);
		
		if(document_openInBrowser($case_document['type'])){
			header('Content-Type:'.document_getMime($case_document['type']).';charset=utf-8');
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
		
		$path=iconv("utf-8","gbk",$_G['case_document_path'].'/'.$id);
		
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
		
		processOrderby($q,'case.time_contract','DESC',array('case.name','lawyers'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'time_contract'=>array('title'=>'案号','td_title'=>'width="180px"','content'=>'<a href="case?edit={id}">{num}</a>'),
			'name'=>array('title'=>'案名','content'=>'{name}'),
			'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"')
		);
		
		$submitBar=array(
		'head'=>'<div class="right">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		exportTable($q,$field,$submitBar);
		
		require 'view/case_list_sidebar.htm';
	}
	
	function write(){
		if(got('case_fee_condition')){
			$id=intval($_POST['id']);
			$value=$_POST['value'];
		
			if($case_feeConditionPrepend=case_feeConditionPrepend($id,$value)){
				echo json_encode($case_feeConditionPrepend);
			}
		}
	}
}
?>