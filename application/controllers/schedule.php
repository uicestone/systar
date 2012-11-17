<?php
class Schedule extends SS_controller{
	
	function __construct(){
		$this->default_method='calendar';
		parent::__construct();
	}
	
	function calendar(){
		$this->load->model('achievement_model','achievement');
		$this->load->model('company_model','company');
		$this->load->model('news_model','news');
		
		$field_news=array(
			'title'=>array(
				'title'=>'公告 <a href="news" style="font-size:14px">更多</a>',
				'wrap'=>array('mark'=>'a','href'=>'javascript:showWindow(\'news/edit/{id}\')'),
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
		
		$table_news=$this->table->setFields($field_news)
			->setData($this->news->getList(5))
			->wrapBox(false)
			->generate();
		
		$this->load->addViewData('table_news',$table_news);

		$sidebar_function=$this->config->item('syscode').'_'.'schedule_side_table';
		$sidebar_tables=$this->company->$sidebar_function();
		$this->load->addViewData('sidebar_tables',$sidebar_tables);
	}
	
	function mine(){
		$this->lists('mine');
	}
	
	function plan(){
		$this->lists('plan');
	}
	
	function lists($method=NULL){
		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));

		if($this->input->post('review_selected') && is_logged('partner')){
			//在列表中批量审核所选日志
			$this->schedule->review($this->input->post('schedule_check'));
		}
		$field=array(
			'checkbox'=>array('title'=>'<input type="checkbox" name="schedule_checkall">','content'=>'<input type="checkbox" name="schedule_check[{id}]" >','td_title'=>' width="38px"','orderby'=>false),
		
			'case.id'=>array('title'=>'案件','content'=>'{case_name}<p style="font-size:11px;text-align:right;"><a href="/schedule/lists?case={case}">本案日志</a> <a href="/cases/edit/{case}">案件</a></p>','orderby'=>false),
		
			'staff_name'=>array('title'=>'人员','content'=>'<a href="schedule/list?staff={staff}"> {staff_name}</a>','td_title'=>'width="60px"','orderby'=>false),
		
			'name'=>array('title'=>'标题','eval'=>true,'content'=>"
				return '<a href=\"javascript:showWindow(\'schedule/edit/{id}\')\" title=\"{name}\">'.str_getSummary('{name}').'</a>';
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
		if($method=='mine'){
			unset($field['staff_name']);
		}		
		if($this->input->post('export')){
			$field=array(
				'name'=>array('title'=>'标题'),
				'content'=>array('title'=>'内容'),
				'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
					return date('m-d H:i',{time_start});
				",'orderby'=>false),
				'hours_own'=>array('title'=>'自报小时'),
				'staff_name'=>array('title'=>'律师')
			);
		}
		$this->table->setFields($field)
			->setData($this->schedule->getList($method));

		if($this->input->post('export')){
			
			$this->load->model('document_model','document');

			require APPPATH.'third_party/PHPWord/PHPWord.php';
			
			$PHPWord = new PHPWord();
			
			$section = $PHPWord->createSection();
			
			$PHPWord->addTableStyle('schedule_billdoc',array('borderSize'=>1,'borderColor'=>'333','cellMargin'=>100));
			
			$table = $section->addTable('schedule_billdoc');
			
			foreach($this->table->rows as $line_name=>$line){
				$table->addRow();
				foreach($line as $cell_name=>$cell){
					$table->addCell(1750)->addText(strip_tags($cell['data']));
				}
			}
			
			// Save File
			$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
			
			$filename=$_SESSION['username'].$this->config->item('timestamp').'.docx';
			
			$path=iconv('utf-8','gbk','temp/'.$filename);
			
			//$this->document->exportHead($filename);

			$objWriter->save('php://output');
			$this->load->require_head=false;
			$this->load->main_view_loaded=true;
			$this->load->sidebar_loaded=true;
		
		}else{
			$tableView=$this->table->setMenu((is_logged('partner')?'<input type="submit" name="review_selected" value="审核" />':'').'<input type="submit" name="export" value="导出" />','left')
					->wrapForm()
					->generate();
			$this->load->addViewData('list',$tableView);
			$this->load->view('schedule/list');$this->load->main_view_loaded=true;
		}		
	}

	function add(){
		$this->edit();
	}
	
	function edit($id=NULL){
		$this->load->model('cases_model','cases');
		$this->load->model('client_model','client');
		
		$this->getPostData($id,function(){
			if($this->input->get('case')){
				post('schedule/case',intval($this->input->get('case')));
			}
			if($this->input->get('client')){
				post('schedule/client',intval($this->input->get('client')));
			}
		
			if($this->input->get('completed')){
				post('schedule/completed',(int)(bool)$this->input->get('completed'));
		
			}else{
				post('schedule/completed',1);//默认插入的是日志，不是提醒
			}
		});
		
		
		if(!post('schedule/time_start')){
			post('schedule/time_start',$this->config->item('timestamp'));
			post('schedule/time_end',$this->config->item('timestamp')+3600);
		}
		
		$submitable=false;//可提交性，false则显示form，true则可以跳转
		
		if($this->input->post('submit')){
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
			
			if(!strtotime(post('schedule_extra/time_start'))){
				$submitable=false;
				showMessage('开始时间格式错误','warning');
			}else{
				post('schedule/time_start',strtotime(post('schedule_extra/time_start')));
			}
		
			post('schedule/time_end',post('schedule/time_start')+post('schedule/hours_own')*3600);
			
			if($_FILES['file']['name']){
				$storePath=iconv("utf-8","gbk",$this->config->item('case_document_path')."/".$_FILES["file"]["name"]);//存储路径转码
				
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
					if(!post('schedule/document',$this->cases->addDocument(post('schedule/case'),$fileInfo))){
						$submitable=false;
					}
				}
		
				rename(iconv("utf-8","gbk",$this->config->item('case_document_path')."/".$_FILES["file"]["name"]),iconv("utf-8","gbk",$this->config->item('case_document_path')."/".post('schedule/document')));
		
				unset($_SESSION['case']['post']['case_document']);
			}
			
			if(post('schedule/document')){
				$this->db->update('case_document',post('case_document'),"id='".post('schedule/document')."'");
			}
			$this->processSubmit($submitable);
		}
		
		if(post('schedule/time_start')){
			post('schedule_extra/time_start',date('Y-m-d H:i:s',post('schedule/time_start')));
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
		$case_array=$this->cases->getListByScheduleType(post('schedule_extra/type'));
		
		//准备客户数组
		$client_array=$this->client->getListByCase(post('schedule/case'));
		
		$this->load->addViewArrayData(compact('case_array','client_array'));
		
		//获得案名
		post('schedule_extra/case_name',$this->cases->fetch(post('schedule/case'),'name'));
		
		if(post('schedule/client')){
			post('schedule_extra/client_name',$this->client->fetch(post('schedule/client'),'name'));	
		}
		
		if(post('schedule/document')){
			post('case_document',$this->cases->fetchDocument(post('schedule/document')));
		}
		
		$this->load->view('schedule/edit');
		$this->load->main_view_loaded=true;
	}

	function listWrite(){
		if($this->input->post('schedule_list_comment')){
			foreach($this->input->post('schedule_list_comment') as $id => $comment){
				$schedule_list_comment_return=$this->schedule->setComment($id,$comment);
				
				echo $schedule_list_comment_return['comment'];
				
				sendMessage($schedule_list_comment_return['uid'],
		
				$schedule_list_comment_return['comment'].'（日志：'.$schedule_list_comment_return['name'].'收到的点评）',
				'你的日志："'.$schedule_list_comment_return['name'].'"收到点评');
			}
		}
		
		if($this->input->post('schedule_list_hours_checked') || $this->input->post('schedule_list_hours_checked')){
			foreach($this->input->post('schedule_list_hours_checked') as $id => $hours_checked){
				echo $this->schedule->check_hours($id,$hours_checked);
			}
		}
	}
	
	function outPlan(){
		
		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));
		
		$field=Array(
			'staff_name'=>array('title'=>'人员','content'=>'<a href="schedule/lists?staff={staff}"> {staff_name}</a>','td_title'=>'width="60px"'),
		
			'time_start'=>array('title'=>'时间','td_title'=>'width="60px"','eval'=>true,'content'=>"
				return date('m-d H:i',{time_start});
			"),
		
			'place'=>array('title'=>'外出地点','td_title'=>'width="25%"')
		);
		
		$table=$this->table->setFields($field)
					->setData($this->schedule->getOutPlanList())
					->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
	}
	
	function readCalendar($id=NULL){
		if(isset($id)){
			//获取指定的一个日程
			echo json_encode($this->schedule->fetch_single(intval($id)));
		
		}else{
			//获得当前视图的全部日历，根据$this->input->get('start'),$this->input->get('end')(timestamp)
			echo json_encode($this->schedule->fetch_range($this->input->get('start'),$this->input->get('end'),$this->input->get('staff'),$this->input->get('case')));
		}
	}
	
	function workHours(){
		$staffly_workhours=$this->schedule->getStafflyWorkHours();
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

		if(!option('date_range/from')){
			option('date_range/from',date('Y-m-d',$start_of_this_week));
			option('date_range/to',$this->config->item('date'));
		}

		$field=array(
			'staff_name'=>array('title'=>'姓名'),
			'sum'=>array('title'=>'总工作时间'),
			'avg'=>array('title'=>'工作日平均')
		);
		
		$work_hour_stat=$this->table->setFields($field)
				->setData($this->schedule->getStafflyWorkHoursList(option('date/from'),option('date/to')))
				->wrapBox(false)
				->generate();

		$this->load->addViewArrayData(compact('chart_staffly_workhours_catogary','chart_staffly_workhours_series','work_hour_stat'));
	}
	
	function writeCalendar(){
		if(!$this->input->post('id')){//插入新的任务
			echo $this->schedule->add($this->input->post());
			unset($_SESSION['schedule']['post']);
			
		}elseif($this->input->post('action')=='delete'){//删除任务
			$this->schedule->delete($this->input->post('id'));
		
		}elseif($this->input->post('action')=='updateContent'){//更新任务内容
			$this->schedule->update($this->input->post('id'),array(
				'content'=>$this->input->post('content'),
				'experience'=>$this->input->post('experience'),
				'completed'=>$this->input->post('completed'),
				'fee'=>(float)$this->input->post('fee'),
				'fee_name'=>$this->input->post('fee_name'),
				'place'=>$this->input->post('place')
			));
		
		}else{//更新任务时间
			$schedule_id=$this->input->post('id');
			$time_delta=intval($this->input->post('dayDelta'))*86400+intval($this->input->post('minuteDelta'))*60;
			
			if($this->input->post('action')=='resize'){
				$this->schedule->resize($schedule_id,$time_delta,(int)$this->input->post('allDay'));
				
			}elseif($this->input->post('action')=='drag'){
				$this->schedule->drag($schedule_id,$time_delta,(int)$this->input->post('allDay'));
			}
		}
			
	}
	
	function taskBoard()
	{
		$id = $_SESSION['id'];
		$sort_data = $this -> schedule -> getTaskBoardSort($id);
		$task_board = array();
		
		if(count($sort_data) != 0)	//若查询结果不为空，即在数据库表中获得当前用户的排列方式
		{	//墙的每一列
			foreach ($sort_data as $series)
			{
				$series_array = array();
				
				if(is_array($series))
				{	//每一列的每个任务
					foreach ($series as $task)
					{
						$task_array = array();
						
						$task_id = str_replace('task_' , '' , $task);
						$fetch_result = $this -> schedule -> fetch($task_id);
						$task_array['id']=$task_id;
						$task_array['title'] = $fetch_result['name'];
						$task_array['content'] = $fetch_result['content'];
	
						array_push($series_array , $task_array);
					}
				}
				array_push($task_board , $series_array);
			}
		}
		
		$this -> load -> addViewData('task_board' , $task_board);
	}
	
	function setTaskBoardSort()
	{
		$sort_data = json_encode($this -> input -> post('sortData'));

		$this -> schedule -> setTaskBoardSort($sort_data , $_SESSION['id']);
		
		$this -> load -> require_head = false;
		echo "success";
	}
	
	function addToTaskBoard($task_id , $uid=NULL)
	{	
		if(is_null($uid)){
			$uid=$_SESSION['id'];
		}
		//获得任务
		$fetch_result = $this -> schedule -> fetch($task_id);
			
		$task_array['id']=$task_id;
		$task_array['title'] = $fetch_result['name'];
		$task_array['content'] = $fetch_result['content'];
		//取第一列任务墙
		$sort_data = $this -> schedule -> getTaskBoardSort($uid);
		
		if(count($sort_data) != 0)
		{	//将任务加入墙的第一列末尾
			$first_series = $sort_data[0];
			array_push($first_series , $task_array);
			$sort_data[0] = $first_series;
			
			$this -> schedule -> setTaskBoardSort(json_encode($sort_data), $uid);
		}
		else	//查询结果为空，即数据库表中没有该用户的任务墙记录，则新增一条记录
		{
			$first_series = array();
			array_push($first_series , $task_array);
			array_push($sort_data , $first_series);
			
			$this -> schedule -> createTaskBoard(json_encode($sort_data), $uid);
		}
		
		$this -> load -> require_head = false;
		echo "success";
	}
}
?>