<?php
class Staff extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		if(is_posted('grade')){
			option('grade',$_POST['grade']);
		}
		
		$q="SELECT staff.id,staff.name,staff.title,staff.modulus,staff.timing_fee_default,
				course.name AS course_name,
				position.ui_name AS position_name
			FROM staff LEFT JOIN course ON staff.course=course.id
				LEFT JOIN position ON staff.position=position.id
			WHERE staff.company='".$this->config->item('company')."'
		";
		
		$search_bar=$this->processSearch($q,array('name'=>'姓名'));
		
		$this->processOrderby($q,'staff.id','ASC');
		
		$listLocator=$this->processMultiPage($q);
		
		if($this->config->item('company_type')=='school'){
			$field=array(
				'id'=>array('title'=>'姓名','content'=>'{name}'),
				'course_name'=>array('title'=>'学科'),
				'status'=>array('title'=>'职称')
			);
		}else{
			$field=array(
				'id'=>array('title'=>'姓名','content'=>'{name}'),
				'position_name'=>array('title'=>'职位','content'=>'{position_name}'),
				'modulus'=>array('title'=>'团奖系数'),
				'timing_fee_default'=>array('title'=>'默认小时费率')
			);
		}
		
		$menu=array(
			'head'=>'<div class="right">'.
						$listLocator.
					'</div>'
		);
		
		$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
		
		$table=$this->fetchTableArray($q, $field);
		
		$this->view_data+=compact('table','menu');
		
		$this->load->view('lists',$this->view_data);
	}
}
?>