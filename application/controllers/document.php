<?php
class Document extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		if(!sessioned('currentPath',NULL,false))
			$_SESSION['document']['currentPath']=$this->config->item('document_root');
		
		if(!sessioned('currentDir',NULL,false))
			$_SESSION['document']['currentDir']='root';
			
		if(!sessioned('currentDirID',NULL,false))
			$_SESSION['document']['currentDirID']=1;
			
		if(!sessioned('upID',NULL,false))
			$_SESSION['document']['upID']='';
		
		$q="SELECT *
			FROM `document` 
			WHERE 1=1 ";
		
		$search_bar=$this->processSearch($q,array('name'=>'文件名'));
		
		$q.=(option('in_search_mod')?'':"AND parent='".$_SESSION['document']['currentDirID']."'").'';
		
		$this->processOrderby($q,'type','ASC');
			
		$listLocator=$this->processMultiPage($q);
		
		$field=option('in_search_mod')?
			array(
				'checkbox'=>array('title'=>'','content'=>'<input type="checkbox" name="document[{id}]" >','td_title'=>'width=38px'),
				'type'=>array(
					'title'=>'类型',
					'eval'=>true,
					'content'=>"
						if('{type}'==''){
							\$image='folder';
						}elseif(is_file('web/images/file_type/{type}.png')){
							\$image='{type}';
						}else{
							\$image='unknown';
						}
						return '<img src=\"images/file_type/'.\$image.'.png\" alt=\"{type}\" />';
					",
					'td_title'=>'width="70px"'
				),
				'name'=>array('title'=>'文件名','td_title'=>'width="150px"','surround'=>array('mark'=>'a','href'=>'/document?view={id}')),
				'path'=>'路径','comment'=>'备注'
			)
			:
			array(
				'checkbox'=>array('title'=>'','content'=>'<input type="checkbox" name="document[{id}]" >','td_title'=>' width=38px'),
				'type'=>array(
					'title'=>'类型',
					'eval'=>true,
					'content'=>"
						if('{type}'==''){
							\$image='folder';
						}elseif(is_file('images/file_type/{type}.png')){
							\$image='{type}';
						}else{
							\$image='unknown';
						}
						return '<img src=\"/images/file_type/'.\$image.'.png\" alt=\"{type}\" />';
					",
					'td_title'=>'width="55px"'
				),
				'name'=>array('title'=>'文件名','td_title'=>'width="150px"','surround'=>array('mark'=>'a','href'=>'/document?view={id}')),
				'username'=>array('title'=>'上传者','td_title'=>'width="70px"'),
				'comment'=>'备注'
			);
		
		$menu=array(
			'head'=>'<div class="left">'.
						'<input type="submit" name="fav" value="收藏" />'.
						($_SESSION['document']['currentDirID']>1?"<button type='button' name='view' value='0' onclick='redirectPara(this)'>顶级</button><button type='button' name='view' value='".$_SESSION['document']['upID']."' onclick='redirectPara(this)'>上级</button>":'').
						(option('in_search_mod')?'':$_SESSION['document']['currentPath']).
					'</div>'.
					'<div class="right">'.
						$listLocator.
					'</div>',
		);
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->view_data+=compact('table','menu');
		
		$this->load->view('lists',$this->view_data);
	}

	function createDir(){
		$dirPath=iconv("utf-8","gbk",$_SESSION['document']['currentPath']."/".$_POST['dirName']);
		mkdir($dirPath);
		$dir=array(
			'name'=>$_POST['dirName'],
			'parent'=>$_SESSION['document']['currentDir'],
			'level'=>$_SESSION['document']['currentLevel'],
			'path'=>$_SESSION['document']['currentPath']."/".$_POST['dirName'],
			'parent'=>$_SESSION['document']['currentDirID'],
			'type'=>''
		);
		db_insert('document',$dir);
		
		redirect('document');
	}

	function download(){
		$file=db_fetch_first("SELECT * FROM document WHERE id = '".intval($_GET['view'])."'");
		
		document_exportHead($file['name']);
		
		$path=$file['path'];
		$path=iconv("utf-8","gbk",$path);
		readfile($path);
		exit;
	}
	
	function favDelete(){
		$_POST=array_trim($_POST);
		unset($_POST['favDelete']);
		print_r($_POST);
		if(isset($_POST)){
			$condition = db_implode($_POST, $glue = ' OR ','file','=',"'","'", '`','key');
			$q="DELETE FROM document_fav WHERE (".$condition.") AND uid='".$_SESSION['id']."'";
			db_query($q);
		}
		redirect('document');
	}
	
	function fav(){
		$_POST=array_trim($_POST);
		if(isset($_POST)){
			$glue=$values='';
			foreach($_POST['document'] as $id=>$status){
				$values.=$glue."('".$id."','".$_SESSION['id']."','".time()."')";
				$glue=','."\n";
			}
			$q="REPLACE INTO document_fav (file,uid,time) values ".$values;
			db_query($q);
		}
		redirect('document');
	}
	
	function upload(){
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
				'comment'=>$_POST['comment'],
				'uid'=>$_SESSION['id'],
				'username'=>$_SESSION['username'],
				'time'=>$this->config->item('timestamp')
			);
			db_insert('document',$fileInfo,false,$db_replace);
			redirect('document');
		}
	}
}
?>