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
			'abbreviation'=>array('title'=>'名称','content'=>'<input type="checkbox" name="contact_check[{id}]" />
			<a href="javascript:showWindow(\'contact/edit/{id}\')" title="{name}">{abbreviation}</a>',
				'td'=>'class="ellipsis"'
			),
			'work_for'=>array('title'=>'单位'),
			'position'=>array('title'=>'职务'),
			'phone'=>array('title'=>'电话','td'=>'class="ellipsis" title="{phone}"'
			),
			'address'=>array('title'=>'地址','td'=>'class="ellipsis" title="{address}"'
			),
			'comment'=>array('title'=>'备注','td'=>'class="ellipsis"','eval'=>true,'content'=>"
				return str_getSummary('{comment}',50);
			",
			)
		);
		$table=$this->table->setFields($field)
			->setMenu('<input type="submit" name="delete" value="删除" />','left')
			->wrapForm()
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