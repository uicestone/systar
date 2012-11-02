<?php
class News extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$q="
			SELECT * FROM news WHERE display=1 AND company='".$this->config->item('company')."'
		";
		
		$this->processOrderby($q,'time','DESC');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'time'=>array('title'=>'日期','td_title'=>'width="80px"','eval'=>true,'content'=>"
				return date('m-d',{time});
			"),
			'title'=>array('title'=>'标题','content'=>'<a href="javascript:showWindow(\'news?edit={id}\')">{title}</a>'),
			'username'=>array('title'=>'发布人')
		);
		
		$menu=array(
		'head'=>'<div style="float:right;">'.
					$listLocator.
				'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->view_data+=compact('table','menu');
		
		$this->load->view('lists',$this->view_data);
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
		
		if(is_posted('submit')){
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