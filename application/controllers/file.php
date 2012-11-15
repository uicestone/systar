<?php
class File extends SS_controller{
	function __construct(){
		parent::__construct();
		$this->load->model('cases_model','cases');
	}
	
	function lists(){
		$field=array(
			'num'=>array('title'=>'案号','td_title'=>'width="180px"','content'=>'<a href="case?edit={id}">{num}</a>'),
			'case_name'=>array('title'=>'案名'),
			'time_contract'=>array('title'=>'收案时间'),
			'time_end'=>array('title'=>'结案时间'),
			'lawyers'=>array('title'=>'主办律师','td_title'=>'width="100px"'),
			'status'=>array('title'=>'状态','td'=>'title="{status_time}"','eval'=>true,'content'=>"
				return \$this->cases->getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			"),
			'staff_name'=>array('title'=>'人员')
		);
		
		$list=$this->table->setFields($field)
				->setData($this->cases->getFiledList())
				->generate();
		
		$this->load->addViewData('list', $list);
		$this->load->view('list');
	}

	function addStatus(){
		
	}
	
	function tobe(){
		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));
		
		$field=array(
			'num'=>array('title'=>'案号','content'=>'<a href="case?edit={id}">{num}</a>','td_title'=>'width="180px"'),
			'name'=>array('title'=>'案名','content'=>'{name}'),
			'time_contract'=>array('title'=>'收案时间'),
			'time_end'=>array('title'=>'结案时间'),
			'lawyers'=>array('title'=>'主办律师'),
			'status'=>array('title'=>'状态','td_title'=>'width="75px"','td'=>'title="{status_time}"','eval'=>true,'content'=>"
				return \$this->cases->getStatus('{is_reviewed}','{locked}',{apply_file},{is_query},{finance_review},{info_review},{manager_review},{filed},'{contribute_sum}','{uncollected}').' {status}';
			")
		);

		$list=$this->table->setFields($field)
				->setData($this->cases->getTobeFiledList())
				->generate();
		
		$this->load->addViewData('list', $list);
		
		$this->load->view('list');
	}
	
	function view(){
		$q="SELECT *,FROM_UNIXTIME(time,'%Y-%m-%d') AS time 
			FROM `file`,`file_status` 
			WHERE file.id=file_status.file 
				AND file.id='".$this->input->get('view')."'";
		
		$this->processOrderby($q,'time','DESC');
		
		$field=Array('file'=>'序号','client'=>'客户','case'=>'案件','lawyer'=>'承办律师','status'=>'状态','time'=>'时间','person'=>'借阅人','comment'=>'备注');
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->view_data+=compact('table');
		
		$this->load->view('lists',$this->view_data);
	}
}
?>