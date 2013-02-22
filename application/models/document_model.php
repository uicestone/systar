<?php
class Document_model extends SS_Model{
	
	var $id;
	
	var $fields=array(
		'name'=>'文件名',
		'extname'=>'扩展名',
		'size'=>'大小',
		'comment'=>'备注'
	);
	
	function __construct(){
		parent::__construct();
		$this->load->library('filetype');
	}
	
	function fetch($id){
		$id=intval($id);
		
		$query="
			SELECT * 
			FROM `document` 
			WHERE id=$id AND company={$this->company->id}
		";
		return $this->db->query($query)->row_array();
	}
	
	function add(array $data=array()){
		$data=array_intersect_key($data, $this->fields);
		
		$data+=uidTime(true,true);
		
		$this->db->insert('document',$data);
		
		return $this->db->insert_id();
				
	}
	
	function getMime($file_extension){
		$file_extension=strtolower($file_extension);
		$filetype=new Filetype();
		return $filetype->type[$file_extension];
	}
	
	/**
	 * Deprecated 弃用
	 */
	function openInBrowser($file_extension){
		$file_extension=strtolower($file_extension);
		$filetype=new Filetype();
		if(in_array($file_extension,$filetype->open_in_browser)){
			return true;
		}else{
			return false;
		}
	}
	
	function getExtension($filename){
		if(preg_match('/\.(\w*?)$/',$filename,$extname_match)){
			return $extname_match[1];
		}else{
			return '';
		}
	}
	
	function exportHead($filename,$as_attachment=false){
		$ua = $_SERVER["HTTP_USER_AGENT"];
		
		$encoded_filename = urlencode($filename);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);
		
		$extension=$this->getExtension($filename);
		
		if($as_attachment){
			set_time_limit(0); //防止下载超时  

			header("Content-Type: application/force-download"); //强制弹出保存对话框  
			header("Pragma: no-cache"); // 缓存  
			header("Expires: 0");  
			header("Content-Transfer-Encoding: binary");  
			//header("Content-Length: ".$filesize); //文件大小  
		}else{
			header('Content-Type: '.$this->getMime($extension).';charset=utf-8');
		}

		if (preg_match("/MSIE/", $ua)) {
			header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
		}else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		}else {
			header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
	}
	
	function getList(){
		$q="SELECT *
			FROM `document` 
			WHERE 1=1 ";
		$q=$this->search($q,array('name'=>'文件名'));
		$q.=(option('in_search_mod')?'':"AND parent='".$_SESSION['document']['currentDirID']."'").'';
		$q=$this->orderBy($q,'type','ASC');
		$q=$this->pagination($q);
		return $this->db->query($q)->result_array();
	}

}
?>