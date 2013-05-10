<?php
class Document_model extends BaseItem_model{
	
	static $fields=array(
		'name'=>'名称',
		'filename'=>'文件名',
		'extname'=>'扩展名',
		'size'=>'大小',
		'comment'=>'备注'
	);
	
	function __construct(){
		parent::__construct();
		$this->table='document';
		$this->load->library('filetype');
	}
	
	/**
	 * 
	 * @param array $args
	 * project
	 * message
	 * @return array
	 */
	function getList($args=array()){
		
		if(isset($args['project'])){
			$this->db->join('project_document',"project_document.document = document.id AND project_document.project = {$args['project']}",'INNER');
		}
		
		if(isset($args['message'])){
			$this->db->join('message_document',"message_document.document = document.id AND message_document.message = {$args['message']}",'inner');
		}
		
		return parent::getList($args);
	}
	
	function add(array $data=array()){
		$document=array_intersect_key($data, self::$fields);
		
		$document+=uidTime(true,true);
		
		$this->db->insert('document',$document);
		
		return $this->db->insert_id();
	}
	
	function update($id,$data=array()){
		
		$id=intval($id);
		
		$document=array_intersect_key($data, self::$fields);
		
		$document+=uidTime(false);
		
		return $this->db->update('document',$document,array('id'=>$id));
	}
	
	function delete($id){
		$id=intval($id);
		return $this->db->delete('document',array('id'=>$id));
	}
	
	function getMime($file_extension){
		$file_extension=strtolower($file_extension);
		$filetype=new Filetype();
		if(isset($filetype->type[$file_extension])){
			return $filetype->type[$file_extension];
		}
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
	
}
?>