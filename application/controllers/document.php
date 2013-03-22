<?php
class Document extends SS_controller{
	
	var $list_args;
	
	var $section_title='文件';
	
	function __construct(){
		parent::__construct();
		
		$controller=CONTROLLER;
		
		$this->list_args=array(
			'name'=>array('heading'=>'文件名','cell'=>'<a href="/document/download/{id}">{name}</a>'),
			'time_insert'=>array('heading'=>'上传时间','parser'=>array('function'=>function($time_insert){return date('Y-m-d H:i:s',$time_insert);},'args'=>array('{time_insert}'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->$controller,'getCompiledLabels'),'args'=>array('{id}')))
		);
	}
	
	function index(){
		
		//监测有效的名称选项
		if($this->input->post('name')!==false && $this->input->post('name')!==''){
			option('search/name',$this->input->post('name'));
		}
		
		if(is_array($this->input->post('labels'))){
			
			if(is_null(option('search/labels'))){
				option('search/labels',array());
			}
			
			option('search/labels',array_trim($this->input->post('labels'))+option('search/labels'));
		}
		
		//点击了取消搜索按钮，则清空session中的搜索项
		if($this->input->post('submit')==='search_cancel'){
			option('search/labels',array());
			option('search/name',NULL);
		}
		
		//提交了搜索项，但搜索项中没有labels项，我们将session中搜索项的labels项清空
		if($this->input->post('submit')==='search' && $this->input->post('labels')===false){
			option('search/labels',array());
		}
		
		$table=$this->table->setFields($this->list_args)
			->setData($this->document->getList(option('search')))
			->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
		
		if(file_exists(APPPATH.'/views/'.CONTROLLER.'/list_sidebar'.EXT)){
			$this->load->view(CONTROLLER.'/list_sidebar',true,'sidebar');
		}else{
			$this->load->view('document/list_sidebar',true,'sidebar');
		}

	}

	function download($id){
		$this->output->as_ajax=false;

		$document=$this->document->fetch($id);
		
		$this->document->exportHead($document['name']);
		
		$filename=$this->config->item('document_path').$document['id'];
		
		$filename=iconv("utf-8","gbk",$filename);
		readfile($filename);
	}
	
	function favDelete(){
		$fav_to_detele=array_trim($this->input->post());
		unset($fav_to_detele['favDelete']);
		if(isset($fav_to_detele)){
			$condition = db_implode($_POST, $glue = ' OR ','file','=',"'","'", '`','key');
			$q="DELETE FROM document_fav WHERE (".$condition.") AND uid={$this->user->id}";
			$this->db->query($q);
		}
		redirect('document');
	}
	
	function fav(){
		$_POST=array_trim($_POST);
		if(isset($_POST)){
			$glue=$values='';
			foreach($this->input->post('document') as $id=>$status){
				$values.=$glue."('".$id."',{$this->user->id},'".time()."')";
				$glue=','."\n";
			}
			$q="REPLACE INTO document_fav (file,uid,time) values ".$values;
			$this->db->query($q);
		}
		redirect('document');
	}
	
	function upload(){
		/*
if ($_FILES["file"]["error"] > 0){
			echo "error code: " . $_FILES["file"]["error"] . "<br />";
		}
		else{
			$storePath=iconv("utf-8","gbk",$_SESSION['document']['currentPath']."/".$_FILES["file"]["name"]);//存储路径转码
			
			if (is_file($storePath)){
				unlink($storePath);
				$db_replace=true;
			}else{
				$db_replace=false;
			}
			
			move_uploaded_file($_FILES["file"]["tmp_name"], $storePath);
		
			if(preg_match('/\.(\w*?)$/',$_FILES["file"]["name"], $extname_match)){
				$_FILES["file"]["type"]=$extname_match[1];
			}else
				$_FILES["file"]["type"]='none';
			$fileInfo=array(
				'name'=>$_FILES["file"]["name"],
				'type'=>$_FILES["file"]["type"],
				'size'=>$_FILES["file"]['size'],
				'parent'=>$_SESSION['document']['currentDirID'],
				'path'=>$_SESSION['document']['currentPath']."/".$_FILES["file"]["name"],
				'comment'=>$this->input->post('comment'),
				'uid'=>$this->user->id,
				'username'=>$_SESSION['username'],
				'time'=>$this->date->now
			);
			//db_insert('document',$fileInfo,false,$db_replace);
			redirect('document');
		}
*/
		$this->load->view('document/document.php');
	}
	
	function submit(){
		error_reporting(E_ALL | E_STRICT);
		echo dirname('../temp');
		require(APPPATH.'third_party/'.'blueimp/'.'UploadHandler.php');
		$upload_handler = new UploadHandler();
	}
}
?>