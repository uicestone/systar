<?php
class Template extends SS_Controller{
	
	var $template=array();
	
	function __construct() {
		parent::__construct();
		$this->output->set_content_type('application/json');
	}
	
	function people($page='index'){
		if($page==='index'){
			$this->template=array(
				array(
					'type'=>'fields',
					'data'=>array(
						array(
							'name'=>'name',
							'type'=>'text',
							'value'=>'<%=element.name%>',
							'label'=>'姓名'
						),
						array(
							'name'=>'id_card',
							'type'=>'text',
							'value'=>'<%=element.id_card%>',
							'label'=>'身份证'
						),
					),
				),
				array(
					'type'=>'list',
					'data'=>array(
						'source'=>'people.meta',
						'limit'=>10,
						'order_by'=>'id desc',
						'fields'=>array(
							array(
								'heading'=>'资料项',
								'cell'=>array('data'=>'<%=element.name%>')
							),
							array(
								'heading'=>'名称',
								'cell'=>array('data'=>'<%=element.content%>')
							)
						)
					),
				),
				array(
					'type'=>'list',
					'data'=>array(
						'source'=>'people.relative',
						'where'=>array('type'=>'people'),
						'limit'=>10,
						'order_by'=>'id desc',
						'fields'=>array(
							array(
								'heading'=>'名称',
								'cell'=>array('data'=>'<a href="#<%=element.type%>/<%=element.id%>"><%=element.name%></a>')
							),
							array(
								'heading'=>'关系',
								'cell'=>array('data'=>'<%=element.relation%>')
							)
						)
					),
				),
				array(
					'type'=>'list',
					'data'=>array(
						'source_type'=>'uri',
						'uri'=>'/schedule',
						'args'=>array(
							'people'=>'<%=element.id%>'//当前页面正在编辑对象的id，如#people/1即为1
						),
						'limit'=>10,
						'order_by'=>'id desc',
						'fields'=>array(
							array(
								'heading'=>'标题',
								'cell'=>array('data'=>'<%=element.name%>')
							),
							array(
								'heading'=>'时间',
								'cell'=>array('data'=>'<%=element.datetime%>')
							),
						)
					),
				),
			);
		}
		
		$this->output->set_output(json_encode($this->template));
		
	}
}
?>
