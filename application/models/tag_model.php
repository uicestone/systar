<?php
class Tag_model extends Object_model{
	function __construct() {
		parent::__construct();
		$this->table='tag';
	}
	
	/**
	 * 测试一个标签名
	 * 如果存在则返回id
	 * 如果不存在则添加后返回id
	 * @param string $name
	 */
	function match($name){
		$name=urldecode($name);
		
		$row=$this->db->get_where('tag', array('name'=>$name))->row_array();
		if($row){
			return $row['id'];
		}else{
			$this->db->insert('tag',array('name'=>$name));
			return $this->db->insert_id();
		}
	}
	
	function getList(array $args=array()){
		$args['company']=$args['display']=false;
		return parent::getList($args);
	}
	
	/**
	 * 把一个搜索字符串切分成分词，通过标签匹配，返回相关度最高的相关项目
	 * @param string $tag_string
	 * @return array 
	 * array(
	 *	0=>array(
	 *		'type'=>'client',
	 *		'id'=>3
	 *	),
	 *	1=>array(
	 *		'type'=>'case',
	 *		'id'=>5
	 *	)
	 * )
	 * Test case:Test::tagSearch()
	 */
	function search($tag_string){
		$non_white_space_strings=explode(' ',$tag_string);
		$keywords=array();
		foreach($non_white_space_strings as $non_white_space_string){
			$string_length=strlen($non_white_space_string);
			if($string_length>0){
				$partial_keywords=$this->wordSplit($non_white_space_string);
				$keywords=array_merge($keywords,$partial_keywords);
			}
		}
		$keywods_count=count($keywords);
		if($keywods_count>0){
			$sorted_results=$this->CDS($keywords);
		}
		else{
			$sorted_results=false;
		}
		return $sorted_results;
	}
	
	/**
	 * 分词
	 * @param string $source_string 源字符串
	 * @return array 分好的单词数组
	 */
	private function wordSplit($source_string){
		$words=array();
		$pscws_path=APPPATH.'third_party/pscws4/';
		require_once($pscws_path.'pscws4.class.php');
		$dicts=array(
			'UTF-8'=>$pscws_path.'dict.utf8.xdb'
		);
		$rules=array(
			'UTF-8'=>$pscws_path.'etc/rules.utf8.ini'
		);
		$charset=strtoupper($this->config->item('charset'));
		$pscws=new PSCWS4($charset);
		$dict=$dicts[$charset];
		$rule=$rules[$charset];
		$pscws->set_dict($dict);
		$pscws->set_rule($rule);
		$pscws->set_ignore(true);
		$pscws->send_text($source_string);
		for($some_words=$pscws->get_result();$some_words!==false;$some_words=$pscws->get_result()){
			foreach($some_words as $one_word){
				$words[]=$one_word['word'];
			}
		}
		$pscws->close();
		return $words;
	}
	
	/**
	 * 相关度匹配排序
	 * @param array $keywords 关键字数组
	 * @return array 已按相关度降序排序的项目（id,type） 
	 */
	private function CDS(array $keywords){
		$sql='call init_CDS();';
		$this->db->simple_query($sql);
		foreach($keywords as $keyword){
			$sql='insert into keywords_table values(\''.$keyword.'\');';
			$this->db->simple_query($sql);
		}
		$user_id=$this->user->id;
		$only_this=0;
		$sql='call case_CDS('.$user_id.','.$only_this.');';
		$this->db->simple_query($sql);
		$sql='select ct.id as id,ct.column_name as type from CD_table ct order by ct.degree desc,ct.matches desc;';
		$sorted_results=$this->db->query($sql)->result_array();
		$sql='call finalize_CDS();';
		$this->db->simple_query($sql);
		return $sorted_results;
	}
	
	/**
	 * 接受一个tag name，返回与其相关的tag的id和name构成的数组
	 * @param type $tag
	 * @param type $relation
	 */
	function getRelatives($tag,$relation=NULL){
		
		$this->db->select('relative.id,relative.name')
			->from('tag_relationship')
			->join('tag','tag.id=tag_relationship.tag','inner')
			->join('tag relative','relative.id=tag_relationship.relative','inner')
			->or_where(array('tag.name'=>$tag,'tag.id'=>$tag));

		return array_column($this->db->get()->result_array(),'name','id');
	}
}

?>
