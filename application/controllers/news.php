<?php
class News extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		$field=array(
			'time'=>array('title'=>'日期','td_title'=>'width="80px"','eval'=>true,'content'=>"
				return date('m-d',{time});
			"),
			'title'=>array('title'=>'标题','content'=>'<a href="javascript:showWindow(\'news/edit/{id}\')">{title}</a>'),
			'username'=>array('title'=>'发布人')
		);
		
		$list=$this->table->setField($field)
			->setData($this->news->getList())
			->generate();
		
		$this->load->addViewData($list);
		$this->load->view('list');

		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));
	}
	
	function add(){
		$this->edit();
	}
	
	function edit($id=NULL){
		$this->getPostData($id);
		
		//取得数据
		$q_news="SELECT * FROM news WHERE id='".post(''.CONTROLLER.'/id')."'";
		$r_news=db_query($q_news);
		if(db_rows($r_news)==0){
			showMessage('新闻不存在','warning');exit;
		}
		post('news',db_fetch_array($r_news));
		
		$submitable=false;//可提交性，false则显示form，true则可以跳转
		
		if($this->input->post('submit')){
			$submitable=true;
			
			$_SESSION[CONTROLLER]['post'][CONTROLLER]=array_replace_recursive($_SESSION[CONTROLLER]['post'][CONTROLLER],array_trim($_POST[CONTROLLER]));
			
			if(array_dir('_POST/'.CONTROLLER.'/title')==''){
				$submitable=false;
				showMessage('请填写标题','warning');
			}
			
			$this->processSubmit($submitable);
		}
	}
}
?>