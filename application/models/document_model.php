<?php
class Document_model extends Object_model{
	
	static $fields;

	function __construct(){
		parent::__construct();
		$this->table='document';
		parent::$fields['type']=$this->table;
		self::$fields=array(
			'filename'=>'',//文件名
			'extname'=>'',//扩展名
			'size'=>0,//大小
			'comment'=>NULL//备注
		);

		$this->mod=true;
		$this->load->library('filetype');
	}
	
	/**
	 * 
	 * @param array $args
	 *	project
	 *	message
	 * @return array
	 */
	function getList($args=array()){
		
		if(isset($args['project'])){
			$args['is_relative_of']=$args['project'];
		}
		
		if(isset($args['message'])){
			$this->db->join('message_document',"message_document.document = document.id AND message_document.message = {$args['message']}",'inner');
		}
		
		return parent::getList($args);
	}
	
	function add($id,$data){
		$insert_id=parent::add($data);
		
		$data=array_merge(self::$fields,array_intersect_key($data,self::$fields));
		$data['id']=$insert_id;
		$this->db->insert($this->table,$data);
		
		return $insert_id;
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
	
	function getPeopleMod($id,$people){
		$id=intval($id);
		$this->db->select('mod')->from('document_mod')->where("people{$this->db->escape_int_array($people)}",NULL,false);
		$mods=array_column($this->db->get()->result_array(),'mod');
		$mod=array_reduce($mods, function($mod, $next_mod){
			return $mod|$next_mod;
		});
		return $mod;
	}
	
	function getModPeople($id,$mod){
		$id=intval($id);
		$mod=intval($mod);
		$this->db->select('people')
			->from('document_mod')
			->where('document',$id)
			->where("`mod` & $mod = $mod",NULL,false);
		
		$people=array_column($this->db->get()->result_array(),'people');
		
		return $people;
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