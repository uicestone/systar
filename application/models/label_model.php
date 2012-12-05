<?php
class Label_model extends SS_Model{
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * TODO@iori	这是标签相关度匹配的实现接口
	 * 把一个搜索字符串切分成分词，通过标签匹配，返回相关度最高的相关项目
	 * @param string $label_string
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
	 */
	function search($label_string){
		return array();
	}
}

?>
