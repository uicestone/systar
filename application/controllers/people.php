<?php
class People extends SS_Controller{
	function __construct() {
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	/**
	 * 根据请求的字符串返回匹配的人员id，名称和类别
	 */
	function match(){

		$term=$this->input->post('term');
		
		$result=$this->people->match($term);

		$array=array();

		foreach ($result as $row){
			$array[]=array(
				'label'=>$row['name'].'    '.$row['type'],
				'value'=>$row['id']
			);
		}
		$this->output->data=$array;
	}
	
}
?>
