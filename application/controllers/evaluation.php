<?php
class Evaluation extends SS_controller{
	function __construct(){
		$this->default_method='comment';
		parent::__construct();
	}
	
	function staffList(){
		$this->session->set_userdata('last_list_action',$this->input->server('REQUEST_URI'));

		$field=array(
			'name'=>array('title'=>'姓名','wrap'=>array('mark'=>'a','href'=>'javascript:showWindow(\'evaluation/score/{id}\')')),
			'position.id'=>array('title'=>'职位','content'=>'{position_name}')
		);
		
		$table=$this->table->setFields($field)
			->setData($this->evaluation->getStaffList())
			->generate();
		$this->load->addViewData('list', $table);
		$this->load->view('list');
	}
	
	function comment(){
		$field=array(
			'name'=>array('title'=>'评分项','content'=>'{name}({weight})'),
			'comment'=>array('title'=>'附言'),
			'staff_name'=>array('title'=>'评分人','content'=>'{staff_name}({position_name})')
		);
		
		$table=$this->table->setFields($field)
			->setData($this->evaluation->getCommentList())
			->generate();

		$evaluation_result=array(
			'_field'=>array(
				'互评分',
				'自评分',
				'主管分'
			),
			array(
				$this->evaluation->getPeer(),
				$this->evaluation->getSelf(),
				$this->evaluation->getManager()
			)
		);
		
		$this->load->addViewData('list', $table);
		$this->load->addViewData('evaluation_result', $evaluation_result);
		$this->load->view('list');
	}
	
	function result(){
		$field=array(
			'staff_name'=>array('title'=>'姓名'),
			'each_other'=>array('title'=>'互评','content'=>'{each_other}({critics})'),
			'self'=>array('title'=>'自评'),
			'manager'=>array('title'=>'主管评分')
		);

		$table=$this->table->setFields($field)
			->setData($this->evaluation->getResultList())
			->generate();
		
		$this->load->addViewData('list', $table);
		$this->load->view('list');
	}
	
	function scoreWrite($staff){
		$this->load->require_head=false;
		
		$staff=intval($staff);
		$indicator=intval($this->input->post('indicator'));
		//$anonymous=intval($this->input->post('anonymous'));
		
		$field=$value=NULL;
		
		if($this->input->post('field') && $this->input->post('value')){
			$field=$this->input->post('field');
			$value=$this->input->post('value');
		}

		$evaluation_insert_score=$this->evaluation->insertScore($indicator,$staff,$field,$value/*,$anonymous*/);
		
		if($evaluation_insert_score){
			echo json_encode($evaluation_insert_score);
		}
	}
	
	function score($staff_id){
		$staff_id=intval($staff_id);
		
		$field=array(
			'name'=>array('title'=>'考核指标','td'=>'id="{id}"','content'=>'{name}({weight})','td_title'=>'width="20%"'),
			'score'=>array('title'=>'分数','td_title'=>'width="70px"','eval'=>true,'content'=>"
				if('{score}'==0){
					return '<input type=\"text\" style=\"width:50px;\" />';
				}else{
					return '<span>{score}</span>';
				}
			"),
			'comment'=>array('title'=>'附言','eval'=>true,'content'=>"
				if(!'{comment}'){
					return '<input type=\"text\" />';
				}else{
					return '<span>{comment}</span>';
				}
			")
		);
		
	
		$table=$this->table->setFields($field)
			->setMenu('<button type="button" name="imfeelinglucky">手气不错</button>','left')
			->setData($this->evaluation->getIndicatorList($staff_id))
			->generate();
		
		$this->load->addViewData('list', $table);
		$this->load->view('list');
	}
}
?>