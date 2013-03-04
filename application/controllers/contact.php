<?php
class Contact extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function lists(){
		
		$this->load->model('client_model','client');	    
		if($this->input->post('delete')){
			$contacts_to_delete=array_trim($this->input->post('contact_check'));
			$this->client->delete($contacts_to_delete);
		}
		$field=array(
			'abbreviation'=>array('heading'=>'名称','cell'=>'<input type="checkbox" name="contact_check[{id}]" />
			<a href="javascript:showWindow(\'contact/edit/{id}\')" title="{name}">{abbreviation}</a>',
				'td'=>'class="ellipsis"'
			),
			'work_for'=>array('heading'=>'单位'),
			'position'=>array('heading'=>'职务'),
			'phone'=>array('heading'=>'电话','td'=>'class="ellipsis" title="{phone}"'
			),
			'address'=>array('heading'=>'地址','td'=>'class="ellipsis" title="{address}"'
			),
			'comment'=>array('heading'=>'备注','td'=>'class="ellipsis"','eval'=>true,'cell'=>"
				return str_getSummary('{comment}',50);
			",
			)
		);
		$table=$this->table->setFields($field)
			->setData($this->contact->getList())
			->generate();
		$this->load->addViewData('list',$table);
		$this->load->view('list');
	}

	function add(){
	}
	
	function edit($id=NULL){
	}
}
?>