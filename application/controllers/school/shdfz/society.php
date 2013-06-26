<?php
class Society extends Team{
	function __construct() {
		parent::__construct();
		
		$this->load->model('society_model','society');
		$this->people=$this->society;
		$this->team=$this->society;

		$this->list_args=array(
			'name'=>array('heading'=>'名称'),
			
			'comment'=>array('heading'=>'简介','parser'=>array('function'=>function($id){
				$profiles=array_sub($this->society->getProfiles($id),'content','name');
				if(array_key_exists('简介', $profiles)){
					return $profiles['简介'];
				}
			},'args'=>array('id'))),
			
			'people'=>array('heading'=>'名额/已报','parser'=>array('function'=>function($id){
				$profiles=array_sub($this->society->getProfiles($id),'content','name');
				
				if(array_key_exists('名额', $profiles)){
					return $profiles['名额'].'/'.$this->society->countApplicants($id);
				}
			},'args'=>array('id')))
		);
		
		if(!$this->user->inTeam(13453)){
			$this->list_args['apply']=array('heading'=>'报名','cell'=>array('data'=>'<button type="submit" name="apply" id="{id}">报名</button>'));
		}
		
	}
	
	function index(){
		
		if($this->user->inTeam(13453)){
			$this->config->set_user_item('search/leaded_by', $this->user->id);
		}
		
		parent::index();
	}
	
	function submit($submit, $id, $button_id=NULL){
		
		parent::submit($submit, $id, $button_id);
		
		if($submit==='apply'){
			$this->society->addRelationship($this->society->id, $this->user->id, '报名');
		}
	}
	
}
?>
