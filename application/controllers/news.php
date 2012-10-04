<?php
class News extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$q="
			SELECT * FROM news WHERE display=1 AND company='".$_G['company']."'
		";
		
		processOrderby($q,'time','DESC');
		
		$listLocator=$this->processMultiPage($q);
		
		$field=array(
			'time'=>array('title'=>'日期','td_title'=>'width="80px"','eval'=>true,'content'=>"
				return date('m-d',{time});
			"),
			'title'=>array('title'=>'标题','content'=>'<a href="javascript:showWindow(\'news?edit={id}\')">{title}</a>'),
			'username'=>array('title'=>'发布人')
		);
		
		$submitBar=array(
		'head'=>'<div style="float:right;">'.
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
		getPostData();
		
		//取得数据
		$q_news="SELECT * FROM news WHERE id='".post(''.IN_UICE.'/id')."'";
		$r_news=db_query($q_news);
		if(db_rows($r_news)==0){
			showMessage('新闻不存在','warning');exit;
		}
		post('news',db_fetch_array($r_news));
		
		$submitable=false;//可提交性，false则显示form，true则可以跳转
		
		if(is_posted('submit')){
			$submitable=true;
			
			$_SESSION[IN_UICE]['post'][IN_UICE]=array_replace_recursive($_SESSION[IN_UICE]['post'][IN_UICE],array_trim($_POST[IN_UICE]));
			
			if(array_dir('_POST/'.IN_UICE.'/title')==''){
				$submitable=false;
				showMessage('请填写标题','warning');
			}
			
			processSubmit($submitable);
		}
	}
}
?>