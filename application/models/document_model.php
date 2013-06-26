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
	 *	project
	 *	message
	 * @return array
	 */
	function getList($args=array()){
		
		if(isset($args['project'])){
			$this->db->join('project_document',"project_document.document = document.id AND project_document.project = {$args['project']}",'inner');
		}
		
		if(isset($args['message'])){
			$this->db->join('message_document',"message_document.document = document.id AND message_document.message = {$args['message']}",'inner');
		}
		
		//验证读权限
		$this->db->where("document.id IN (
			SELECT document FROM document_mod
			WHERE (document_mod.people IS NULL OR document_mod.people{$this->db->escape_int_array(array_merge(array_keys($this->user->teams),array($this->user->id)))})
				AND ((document_mod.mod & 1) = 1)
			)
		");
		
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
	
	function getPeopleMod($id,$people){
		$id=intval($id);
		$this->db->select('mod')->from('document_mod')->where("people{$this->db->escape_int_array($people)}",NULL,false);
		$mods=array_sub($this->db->get()->result_array(),'mod');
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
		
		$people=array_sub($this->db->get()->result_array(),'people');
		
		return $people;
	}
	
	function addMod($mod,$people,$document=NULL){
		
		is_null($document) && $document=$this->id;
		
		if($this->db->where('document',$document)->where('people',$people)->count_all_results('document_mod')===0){
			$this->db->insert('document_mod',array(
				'document'=>$document,
				'people'=>$people,
				'mod'=>$mod
			));
			
			return $this->db->insert_id();
		}
		
		$this->db
			->where('document',$document)
			->where('people',$people)
			->set('mod','`mod` | '.intval($mod),false)
			->update('document_mod');
		
		return $this->db->affected_rows();
	}
	
	function removeMod($mod,$people,$document=NULL){
		
		is_null($document) && $document=$this->id;
		
		$this->db
			->where('document',$document)
			->where('people',$people)
			->set('mod','`mod` & ~'.intval($mod),false)
			->update('document_mod');
		
		return $this->db->affected_rows();
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