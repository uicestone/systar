<?php
class Schedule extends SS_controller{
	public $default_method;
	
	function __construct(){
		parent::__construct();
		$this->default_method='calendar';
	}
	
	function calendar(){
		$this->load->model('achievement_model','achievement');
		
		$q_news="SELECT * FROM `news` WHERE display=1 AND company='".$this->config->item('company')."' ORDER BY time DESC LIMIT 5";
		$field_news=array(
			'title'=>array(
				'title'=>'公告 <a href="news" style="font-size:14px">更多</a>',
				'surround'=>array('mark'=>'a','href'=>'javascript:showWindow(\'news?edit={id}\')'),
				'eval'=>true,
				'content'=>"
					\$return='{title}';
					if('{time}'>\$this->config->item('timestamp')-86400*7){
						\$return.=' <img src=\"images/new.gif\" alt=\"new\" />';
					}
					return \$return;
				",
				'orderby'=>false
			),
		);
		
		$table_news=$this->fetchTableArray($q_news, $field_news);
		
		$sidebar_table=array();
		$sidebar_function=$this->config->item('syscode').'_'.'schedule_side_table';

		$sidebar_table=$this->company->$sidebar_function();
				
		$this->data=compact('table_news','sidebar_table');
	}
	
	function mine(){
		$this->lists('mine');
	}
	
	function lists($para=NULL){
		if(is_posted('review_selected') && is_logged('partner')){
			//在列表中批量审核所选日志
			schedule_review_selected();
		}
		
		$q="
			SELECT
				schedule.id,schedule.name,schedule.content,schedule.experience, schedule.time_start,schedule.hours_own,schedule.hours_checked,schedule.comment,schedule.place,
				case.id AS `case`,case.name AS case_name,
				staff.name AS staff_name,staff.id AS staff,
				if(MAX(case_lawyer.role)='督办合伙人',1,0) AS review_permission
		
				#imperfect 2012/7/13 MAX ENUM排序依据为字符串，并非INDEX
		
			FROM schedule INNER JOIN `case` ON schedule.case=case.id
				INNER JOIN case_lawyer ON case.id=case_lawyer.case
				LEFT JOIN staff ON staff.id = schedule.uid
			WHERE case_lawyer.lawyer='".$_SESSION['id']."'
				AND schedule.display=1 AND schedule.completed=".(got('plan')?'0':'1')."
		";
		
		//TODO schedule_list列表效率
		$q_rows="
			SELECT COUNT(schedule.id) 
			FROM schedule
			WHERE 
		";
		
		$condition='';
		if($para=='mine'){
			$condition.=" AND schedule.`uid`='".$_SESSION['id']."'";
		}else{
			if(got('staff')){
				$condition.=" AND schedule.`uid`='".intval($_GET['staff'])."'";
			}
		}

		if(got('case')){
			$condition.=" AND schedule.`case`='".intval($_GET['case'])."'";
		}
			
		if(got('client')){
			$condition.=" AND schedule.client='".intval($_GET['client'])."'";
		}
									
		$q.=$condition;
		
		$search_bar=$this->processSearch($q,array('case.name'=>'案件','staff.name'=>'人员'));
		
		$date_range_bar=$this->dateRange($q,'time_start');
		
		$q.="
			GROUP BY schedule.id
			ORDER BY FROM_UNIXTIME(time_start,'%Y%m%d') ".(got('plan')?'ASC':'DESC').",schedule.uid,time_start ".(got('plan')?'ASC':'DESC')."
		";
		
		$field=array(
			'checkbox'=>array('title'=>'<input type="checkbox" name="schedule_checkall">','content'=>'<input type="checkbox" name="schedule_check[{id}]" >','td_title'=>' width=38px','orderby'=>false),
		
			'case.id'=>array('title'=>'案件','content'=>'{case_name}<p style="font-size:11px;text-align:right;"><a href="schedule?list&case={case}">本案日志</a> <a href="case?edit={case}">案件</a></p>','orderby'=>false),
		
			'staff_name'=>array('title'=>'人员','content'=>'<a href="schedule?list&staff={staff}"> {staff_name}</a>','td_title'=>'width="60px"','orderby'=>false),
		
			'name'=>array('title'=>'标题','eval'=>true,'content'=>"
				return '<a href=\"javascript:showWindow(\'schedule?edit={id}\')\" title=\"{name}\">'.str_getSummary('{name}').'</a>';
			",'orderby'=>false),
		
			'content'=>array('title'=>'内容','eval'=>true,'content'=>"
				return '<div title=\"{content}\">'.str_getSummary('{content}').'&nbsp;'.'</div>';
			",'orderby'=>false),
		
			'schedule_experience'=>array('title'=>'心得','eval'=>true,'content'=>"
				return ({review_permission}||\$_SESSION['id']=='{staff}')?'<div title=\"{experience}\">'.str_getSummary('{experience}').'&nbsp;'.'</div>':'-';
			",'orderby'=>false),
		
			'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
				return date('m-d H:i',{time_start});
			",'orderby'=>false),
		
			'hours_own'=>array('title'=>'时长','td_title'=>'width="55px"','eval'=>true,'content'=>"
				if('{hours_checked}'==''){
					return '<span class=\"hours_own'.({review_permission}?' editable':'').'\" id={id} name=\"hours\" title=\"自报：{hours_own}\">{hours_own}</span>';
				}else{
					return '<span class=\"hours_checked'.({review_permission}?' editable':'').'\" id={id} name=\"hours\" title=\"自报：{hours_own}\">{hours_checked}</span>';
				}
			",'orderby'=>false),
		
			'comment'=>array('title'=>'评语','eval'=>true,'content'=>"
				if({review_permission}){
					return '<textarea name=\"schedule_list_comment[{id}]\" style=\"width:95%;height:70%\">{comment}</textarea>';
				}else{
					if(\$_SESSION['id']=='{staff}'){
						return '<div title=\"{comment}\">'.str_getSummary('{comment}').'&nbsp;'.'</div>';
					}else{
						return '-';
					}
				}
				
			",'orderby'=>false)
		);
		
		if($para=='mine'){
			unset($field['staff_name']);
		}
		
		if(is_posted('export')){
			$field=array(
				'name'=>array('title'=>'标题'),
				'content'=>array('title'=>'内容'),
				'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
					return date('m-d H:i',{time_start});
				",'orderby'=>false),
				'hours_own'=>array('title'=>'自报小时'),
				'staff_name'=>array('title'=>'律师')
			);
		}else{
			$listLocator=$this->processMultiPage($q);
		}
		
		$table=$this->fetchTableArray($q,$field);
		
		if(is_posted('export')){
			$this->load->view('schedule/billdoc');
		
		}else{
			$menu=array(
			'head'=>'<div class="left">'.
						(is_logged('partner')?'<input type="submit" name="review_selected" value="审核" />':'').
						'<input type="submit" name="export" value="导出" />'.
					'</div>'.
					'<div class="right">'.
						$listLocator.
					'</div>'
			);
			
			$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
			
			$this->data+=compact('menu','table','search_bar','date_range_bar');
			
			$this->load->view('schedule/lists',$this->data);
			$this->main_view_loaded=TRUE;
		}
	}
	
	function add(){
		$this->edit();
	}
	
	function edit($id=NULL){
		model('case');
		model('client');
		
		getPostData(function(){
			if(got('case')){
				post('schedule/case',intval($_GET['case']));
			}
			if(got('client')){
				post('schedule/client',intval($_GET['client']));
			}
		
			if(got('completed')){
				post('schedule/completed',(int)(bool)$_GET['completed']);
		
			}else{
				post('schedule/completed',1);//默认插入的是日志，不是提醒
			}
		});
		
		
		if(!post('schedule/time_start')){
			post('schedule/time_start',$this->config->item('timestamp'));
			post('schedule/time_end',$this->config->item('timestamp')+3600);
		}
		
		$submitable=false;//可提交性，false则显示form，true则可以跳转
		
		if(is_posted('submit')){
			$submitable=true;
			
			$_SESSION['schedule']['post']=array_replace_recursive($_SESSION['schedule']['post'],$_POST);
			
			if(array_dir('_POST/schedule/name')==''){
				$submitable=false;
				showMessage('请填写日志名称','warning');
			}
			
			if(post('schedule/case')>10 && post('schedule/case')<=20 && !post('schedule/client')){
				$submitable=false;
				showMessage('没有选择客户','warning');
			}
			
			if(!strtotime(post('schedule/time_start'))){
				$submitable=false;
				showMessage('开始时间格式错误','warning');
			}else{
				post('schedule/time_start',strtotime(post('schedule/time_start')));
			}
		
			post('schedule/time_end',post('schedule/time_start')+post('schedule/hours_own')*3600);
			
			if($_FILES['file']['name']){
				$storePath=iconv("utf-8","gbk",$_G['case_document_path']."/".$_FILES["file"]["name"]);//存储路径转码
				
				move_uploaded_file($_FILES['file']['tmp_name'], $storePath);
			
				if(preg_match('/\.(\w*?)$/',$_FILES['file']['name'], $extname_match)){
					$_FILES['file']['type']=$extname_match[1];
				}else{
					$_FILES["file"]["type"]='none';
				}
				
				$fileInfo=array(
					'name'=>$_FILES["file"]["name"],
					'type'=>$_FILES["file"]["type"],
					'doctype'=>post('case_document/doctype'),
					'size'=>$_FILES["file"]['size'],
					'comment'=>post('case_document/comment'),
				);
				
				if(post('schedule/case')){
					if(!post('schedule/document',case_addDocument(post('schedule/case'),$fileInfo))){
						$submitable=false;
					}
				}
		
				rename(iconv("utf-8","gbk",$_G['case_document_path']."/".$_FILES["file"]["name"]),iconv("utf-8","gbk",$_G['case_document_path']."/".post('schedule/document')));
		
				unset($_SESSION['case']['post']['case_document']);
			}
			
			if(post('schedule/document')){
				db_update('case_document',post('case_document'),"id='".post('schedule/document')."'");
			}
		
			processSubmit($submitable);
		}
		
		//为scheduleType的Radio准备值
		if(post('schedule/case')<=10 && post('schedule/case')>0){
			post('schedule_extra/type',1);
		
		}elseif(post('schedule/case')>10 && post('schedule/case')<20){
			post('schedule_extra/type',2);
		
		}else{
			post('schedule_extra/type',0);
		
		}
		
		//准备案件数组
		$case_array=case_getListByScheduleType(post('schedule_extra/type'));
		
		//准备客户数组
		$client_array=client_getListByCase(post('schedule/case'));
		
		//获得案名
		$q_case="SELECT name FROM `case` WHERE id='".post('schedule/case')."'";
		post('schedule_extra/case_name',db_fetch_field($q_case));
		
		if(post('schedule/client')){
			$q_client="SELECT abbreviation FROM client WHERE id = '".post('schedule/client')."'";
			post('schedule_extra/client_name',db_fetch_field($q_client));	
		}
		
		if(post('schedule/document')){
			post('case_document',db_fetch_first("SELECT name,doctype,comment FROM case_document WHERE id = '".post('schedule/document')."'"));
		}
	}

	function listWrite(){
		if(is_posted('schedule_list_comment')){
			foreach($_POST['schedule_list_comment'] as $id => $comment){
				$schedule_list_comment_return=schedule_set_comment($id,$comment);
				
				echo $schedule_list_comment_return['comment'];
				
				sendMessage($schedule_list_comment_return['uid'],
		
				$schedule_list_comment_return['comment'].'（日志：'.$schedule_list_comment_return['name'].'收到的点评）',
				'你的日志："'.$schedule_list_comment_return['name'].'"收到点评');
			}
		}
		
		if(is_posted('schedule_list_hours_checked') || is_posted('schedule_list_hours_checked')){
			foreach($_POST['schedule_list_hours_checked'] as $id => $hours_checked){
				echo schedule_check_hours($id,$hours_checked);
			}
		}
	}
	
	function outPlan(){
		$q="
			SELECT
				schedule.id AS schedule,schedule.name AS schedule_name,schedule.content AS schedule_content,schedule.experience AS schedule_experience, schedule.time_start,schedule.hours_own,schedule.hours_checked,schedule.comment AS schedule_comment,schedule.place,
				staff.name AS staff_name,staff.id AS staff
			FROM schedule LEFT JOIN staff ON staff.id = schedule.uid
			WHERE schedule.display=1 AND schedule.place<>''
		";
		
		if(got('case') && got('staff')){
			$q.=" AND schedule.`case`='".$_GET['case']."' AND uid='".$_GET['staff']."'";
		
		}elseif(got('case')){
			$q.=" AND schedule.`case`='".$_GET['case']."'";
		
		}elseif(got('staff')){
			$q.=" AND schedule.`uid`='".$_GET['staff']."'";
		
		}
		
		$this->processOrderby($q,'time_start','DESC',array('place'));
		
		$search_bar=$this->processSearch($q,array('staff.name'=>'人员'));
		
		$listLocator=$this->processMultiPage($q);
		
		$field=Array(
			'staff_name'=>array('title'=>'人员','content'=>'<a href="schedule?list&staff={staff}"> {staff_name}</a>','td_title'=>'width="60px"'),
		
			'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
				return date('m-d H:i',{time_start});
			"),
		
			'place'=>array('title'=>'外出地点','td_title'=>'width="25%"')
		);
		
		$menu=array(
		'head'=>'<div style="float:right;">'.
					$listLocator.
				'</div>'
		);
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->data+=compact('table','menu');
		
		$this->load->view('lists');
	}
	
	function readCalendar($id=NULL){
		if(isset($id)){
			//获取指定的一个日程
			echo json_encode($this->schedule->fetch_single(intval($id)));
		
		}else{
			//获得当前视图的全部日历，根据$_GET['start'],$_GET['end'](timestamp)
			echo json_encode($this->schedule->fetch_range($_GET['start'],$_GET['end'],$_GET['staff'],$_GET['case']));
		}
	}
	
	function workHours(){
		if(date('w')==1){//今天是星期一
			$last_week_monday=strtotime("-1 Week Monday");
		}else{
			$last_week_monday=strtotime("-2 Week Monday");
		}
		
		$q_staffly_workhours="
		SELECT staff.name AS staff_name,lastweek.hours AS lastweek,last2week.hours AS last2week
		FROM staff INNER JOIN (
			SELECT uid,SUM(schedule.hours_own) AS hours
			FROM schedule
			WHERE completed=1 AND schedule.time_start >= '".$last_week_monday."' AND schedule.time_start < '".($last_week_monday+86400*7)."'
			GROUP BY uid
		)lastweek ON staff.id=lastweek.uid
		INNER JOIN (
			SELECT uid,SUM(schedule.hours_own) AS hours
			FROM schedule
			WHERE completed=1 AND schedule.time_start >= '".($last_week_monday-86400*7)."' AND schedule.time_start < '".$last_week_monday."'
			GROUP BY uid
		)last2week ON staff.id=last2week.uid
		ORDER BY lastweek DESC"
		;
		
		$staffly_workhours=db_toArray($q_staffly_workhours);
		$chart_staffly_workhours_catogary=json_encode(array_sub($staffly_workhours,'staff_name'));
		$chart_staffly_workhours_series=array(
			array('name'=>'上上周','data'=>array_sub($staffly_workhours,'last2week')),
			array('name'=>'上周','data'=>array_sub($staffly_workhours,'lastweek'))
		);
		$chart_staffly_workhours_series=json_encode($chart_staffly_workhours_series,JSON_NUMERIC_CHECK);
		
		if(date('w')==1){//今天是星期一
			$start_of_this_week=strtotime($this->config->item('date'));
		}else{
			$start_of_this_week=strtotime("-1 Week Monday");
		}
		$start_of_this_month=strtotime(date('Y-m',$this->config->item('timestamp')).'-1');
		$start_of_this_year=strtotime(date('Y',$this->config->item('timestamp')).'-1-1');
		$start_of_this_term=strtotime(date('Y',$this->config->item('timestamp')).'-'.(floor(date('m',$this->config->item('timestamp'))/3-1)*3+1).'-1');
		
		$days_passed_this_week=ceil(($this->config->item('timestamp')-$start_of_this_week)/86400);
		$days_passed_this_month=ceil(($this->config->item('timestamp')-$start_of_this_month)/86400);
		$days_passed_this_term=ceil(($this->config->item('timestamp')-$start_of_this_term)/86400);
		$days_passed_this_year=ceil(($this->config->item('timestamp')-$start_of_this_year)/86400);
		
		$q="
			SELECT staff.name aS staff_name,
				this_week.sum AS this_week_sum,ROUND(this_week.avg,2) AS this_week_avg,
				this_month.sum AS this_month_sum,ROUND(this_month.avg,2) AS this_month_avg,
				this_term.sum AS this_term_sum,ROUND(this_term.avg,2) AS this_term_avg,
				this_year.sum AS this_year_sum,ROUND(this_year.avg,2) AS this_year_avg
			FROM
			(
				SELECT uid,SUM(hours_own) AS sum, SUM(hours_own)/".$days_passed_this_week." AS avg
				FROM schedule 
				WHERE time_start>='".$start_of_this_week."' AND time_start<'".$this->config->item('timestamp')."' 
					AND completed=1 AND display=1
				GROUP BY uid
			)this_week
			INNER JOIN
			(
				SELECT uid,SUM(hours_own) AS sum, SUM(hours_own)/".$days_passed_this_month." AS avg
				FROM schedule 
				WHERE time_start>='".$start_of_this_month."' AND time_start<'".$this->config->item('timestamp')."' 
					AND completed=1 AND display=1
				GROUP BY uid
			)this_month USING(uid)
			INNER JOIN
			(
				SELECT uid,SUM(hours_own) AS sum, SUM(hours_own)/".$days_passed_this_term." AS avg
				FROM schedule 
				WHERE time_start>='".$start_of_this_term."' AND time_start<'".$this->config->item('timestamp')."' 
					AND completed=1 AND display=1
				GROUP BY uid
			)this_term USING(uid)
			INNER JOIN
			(
				SELECT uid,SUM(hours_own) AS sum, SUM(hours_own)/".$days_passed_this_year." AS avg
				FROM schedule 
				WHERE time_start>='".$start_of_this_year."' AND time_start<'".$this->config->item('timestamp')."' 
					AND completed=1 AND display=1
				GROUP BY uid
			)this_year USING(uid)
			INNER JOIN staff ON staff.id=this_week.uid
		";
		
		$this->$this->processOrderby($q,'this_week_sum','DESC');
		
		$field=array(
			'staff_name'=>array('title'=>'姓名'),
			'this_week_sum'=>array('title'=>'本周','content'=>'{this_week_sum}({this_week_avg})'),
			'this_month_sum'=>array('title'=>'本月','content'=>'{this_month_sum}({this_month_avg})'),
			'this_term_sum'=>array('title'=>'本季','content'=>'{this_term_sum}({this_term_avg})'),
			'this_year_sum'=>array('title'=>'本年','content'=>'{this_year_sum}({this_year_avg})')
		);
		
		$work_hour_stat=$this->fetchTableArray($q,$field);
		
		$this->data+=compact('work_hour_stat','chart_staffly_workhours_catogary','chart_staffly_workhours_series');
	}
	
	function writeCalendar(){
		if(!is_posted('id')){//插入新的任务
			echo $this->schedule->add($_POST);
			unset($_SESSION['schedule']['post']);
			
		}elseif(is_posted('action','delete')){//删除任务
			$this->schedule->delete($_POST['id']);
		
		}elseif(is_posted('action','updateContent')){//更新任务内容
			$this->schedule->update($_POST['id'],array(
				'content'=>$_POST['content'],
				'experience'=>$_POST['experience'],
				'completed'=>$_POST['completed'],
				'fee'=>(float)$_POST['fee'],
				'fee_name'=>$_POST['fee_name'],
				'place'=>$_POST['place']
			));
		
		}else{//更新任务时间
			$timeDelta=intval($_POST['dayDelta'])*86400+intval($_POST['minuteDelta'])*60;
			
			if(is_posted('action','resize')){
				$data['hours_own']="_`hours_own`+'".($timeDelta/3600)."'_";
			}elseif(is_posted('action','drag')){
				$data['time_start']="_`time_start`+'".$timeDelta."'_";
			}
			
			$data['all_day']=(int)$_POST['allDay'];
			$data['time_end']="_time_end+'".$timeDelta."'_";
			
			$this->schedule->update($_POST['id'],$data);
		}
	}
}
?>