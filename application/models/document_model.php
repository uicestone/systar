<?php
class Document_model extends SS_Model{
	function __construct(){
		parent::__construct();
		$this->load->library('filetype');
	}
	
	function fetch($id){
		$query="
			SELECT * 
			FROM `document` 
			WHERE id='{$id}' AND company='{$this->config->item('company')}'";
		return $this->db->query($query)->row_array();
	}
	
	function getMime($file_extension){
		$file_extension=strtolower($file_extension);
		$filetype=new Filetype();
		return $filetype->type[$file_extension];
	}
	
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
	
	function exportHead($filename){
		$ua = $_SERVER["HTTP_USER_AGENT"];
		
		$encoded_filename = urlencode($filename);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);
		
		$extension=$this->getExtension($filename);
		
		header('Content-Type: '.$this->getMime($extension).';charset=utf-8');
		
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