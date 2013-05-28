<?php
class Evaluation extends Project{
	
	var $section_title='评价';
	
	function __construct(){
		parent::__construct();
		$this->load->model('evaluation_model','evaluation');
		$this->project=$this->evaluation;
		$this->default_view_method='candidates';
	}
	
	function index(){
		$this->config->set_user_item('search/type', 'evaluation', false);
		parent::index();
	}
	
	function add(){
		parent::add();
	}
	
	function edit($id){
		
		$this->evaluation->id=$id;
		
		$this->load->addViewData('indicator_list', $this->indicatorList());
		
		parent::edit($id);
	}
	
	function submit($submit, $id) {
		parent::submit($submit, $id);
		
		try{
			if($submit=='indicator'){
				$indicator=$this->input->sessionPost('indicator');
				$evaluation_indicator=$this->input->sessionPost('evaluation_indicator');
				$this->evaluation->addIndicator($this->evaluation->id,$indicator,$evaluation_indicator);
				
				$this->output->setData($this->indicatorList(),'indicator-list','content-table','.item[name="indicator"]>.contentTable','replace');
				
				unset($_SESSION[CONTROLLER]['post'][$this->evaluation->id]['indicator']);
				unset($_SESSION[CONTROLLER]['post'][$this->evaluation->id]['evaluation_indicator']);
			}
			
			elseif($submit=='apply_model'){
				if(!$this->input->post('indicator_model')){
					$this->output->message('请选择模版', 'warning');
					throw new Exception;
				}
				$this->evaluation->applyModel($this->evaluation->id, $this->input->post('indicator_model'));
				$this->output->message('评价模版已应用');
				$this->output->setData($this->indicatorList(),'indicator-list','content-table','.item[name="indicator"]>.contentTable','replace');
				$this->output->setData($this->peopleList(),'people-list','content-table','.item[name="people"]>.contentTable','replace');
			}
			
		}catch(Exception $e){
			$e->getMessage() && $this->output->message($e->getMessage(), 'warning');
			$this->output->status='fail';
		}
	}
	
	function indicatorList(){
		$indicator_list_args=array(
			'name'=>array('heading'=>'评分项'),
			'type'=>array('heading'=>'类型','parser'=>array('function'=>function($type){
				switch($type){
					case 'text':return '文字';
					case 'score':return '分数';
				}
			},'args'=>array('type'))),
			'weight'=>array('heading'=>'分值'),
			'candidates'=>array('heading'=>'被评价人角色'),
			'judges'=>array('heading'=>'评价人角色')
		);
		
		$table=$this->table->setFields($indicator_list_args)
			->setAttribute('name','indicator')
			->setData($this->evaluation->getIndicatorList($this->evaluation->id))
			->generate();
		
		return $table;
	}
	
	function candidates($id){
		$this->evaluation->id=$id;
		$this->evaluation->data=$this->evaluation->fetch($this->evaluation->id);
		
		$this->section_title='被评价人 - '.$this->evaluation->data['name'];
		
		$list_args=array(
			'name'=>array('heading'=>'姓名'),
			'role'=>array('heading'=>'角色')
		);
		
		$table=$this->table->setFields($list_args)
			->setRowAttributes(array('hash'=>'evaluation/score/'.$this->evaluation->id.'/{id}'))
			->generate($this->evaluation->getCandidatesList($this->evaluation->id,array('limit'=>'pagination')));
		
		$this->load->addViewData('list', $table);
		
		$this->load->view('list');
	}
	
	function score($evaluation_id,$people_id){
		$this->evaluation->id=$evaluation_id;
		
		$people=$this->people->fetch($people_id);
		$this->evaluation->data=$this->evaluation->fetch($this->evaluation->id);
		
		$this->section_title=$people['name'].' - '.$this->evaluation->data['name'];
		
		$list_args=array(
			'name'=>array('heading'=>'评分指标'),
			'score'=>array('heading'=>'评分/评价','parser'=>array('function'=>function($type,$weight){
				if($type=='score'){
					return '<input type="text" weight="'.$weight.'" placeholder="满分：'.$weight.'" />';
				}else{
					return '<textarea></textarea>';
				}
			},'args'=>array('type','weight')))
		);
		
		$table=$this->table->setFields($list_args)
			->generate($this->evaluation->getIndicatorList($this->evaluation->id, $people_id, $this->user->id, array('limit'=>'pagination')));
		
		$this->load->addViewData('list', $table);
		$this->load->view('list');
		$this->load->view('evaluation/score_sidebar',true,'sidebar');
	}
	
	function comment(){
		$field=array(
			'name'=>array('heading'=>'评分项','cell'=>'{name}({weight})'),
			'comment'=>array('heading'=>'附言'),
			'staff_name'=>array('heading'=>'评分人','cell'=>'{staff_name}({position_name})')
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
	
	/**
	 * TODO 此方法需要MC分离
	 */
	function result(){
		$field=array(
			'staff_name'=>array('heading'=>'姓名'),
			'each_other'=>array('heading'=>'互评','cell'=>'{each_other}({critics})'),
			'self'=>array('heading'=>'自评'),
			'manager'=>array('heading'=>'主管评分')
		);

		$table=$this->table->setFields($field)
			->setData($this->evaluation->getResultList())
			->generate();
		
		$this->load->addViewData('list', $table);
		$this->load->view('list');
	}
	
	function scoreWrite($staff){
		
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
	
}
?>