<?php
class Achievement extends SS_controller{
	
	var $section_title='业绩';
	
	var $list_args=array(
		'case_name'=>array('heading'=>array('data'=>'案件','width'=>'25%'),'cell'=>'<a href="#cases/edit/{case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
		'client_name'=>array('heading'=>'客户'),
		'account_time'=>array('heading'=>'到账时间'),
		'amount'=>array('heading'=>'创收'),
		'contribution'=>array('heading'=>'贡献'),
		'bonus'=>array('heading'=>'奖金'),
		'role'=>array('heading'=>'角色')
	);
	
	function __construct(){
		parent::__construct();
		$this->load->model('account_model','account');
	}
	
	/**
	 * 各团队业绩列表
	 */
	function teams(){
		
		$this->section_title='小组业绩';
		
		$this->config->set_user_item('date/from', $this->date->year_begin,false);
		
		if($this->input->post('date_from')){
			$this->config->set_user_item('date/from', $this->input->post('date_from'));
		}
		
		if($this->input->post('date_to')){
			$this->config->set_user_item('date/to', $this->input->post('date_to'));
		}
		
		//获得小组业绩金额
		$data=array(
		
			'新增创收'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'team',
				'received'=>true,
				'contract_date'=>array('from'=>$this->date->year_begin),
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'orderby'=>'sum desc'
			)),

			'存量创收'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'team',
				'received'=>true,
				'contract_date'=>array('to'=>$this->date->last_year_end),
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),

			'签约'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'team',
				'received'=>false,
				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			))
		);
		
		$joined=array();
		
		foreach($data as $key => $array){
			foreach($array as $row){
				if(!isset($joined[$row['team']])){
					$joined[$row['team']]=array(
						'team_name'=>$row['team_name'],
					);
				}
				$joined[$row['team']][$key]=$row['sum'];
			}
		}
		
		$joined=array_merge($joined,array());
		
		$category=array_sub($joined,'team_name');
		
		$series=array(
			array('name'=>'新增创收','data'=>array_sub($joined,'新增创收',NULL,true),'stack'=>2),
			array('name'=>'存量创收','data'=>array_sub($joined,'存量创收',NULL,true),'stack'=>2),
			array('name'=>'签约','data'=>array_sub($joined,'签约',NULL,true),'stack'=>0)
		);
		
		$this->load->addViewData('category', json_encode($category));
		$this->load->addViewData('series', json_encode($series,JSON_NUMERIC_CHECK));
		
		//获得小组业绩数量
		$this->load->model('project_model','project');
		
		$data=array(
			
			'签约案件'=>$this->project->getList(array(
				'count'=>true,
				'group'=>'team',
				'labels'=>array('案件'),
				'time_contract'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'orderby'=>'count desc'
			)),
			
			'面谈咨询'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('咨询','面谈'),
				'group'=>'team',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
			)),

			'电话咨询'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('咨询','电话'),
				'group'=>'team',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),

			'网络咨询'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('咨询','网络'),
				'group'=>'team',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			))
			
		);
		
		$joined=array();
		
		foreach($data as $key => $array){
			foreach($array as $row){
				if(!isset($joined[$row['team']])){
					$joined[$row['team']]=array(
						'team_name'=>$row['team_name'],
					);
				}
				$joined[$row['team']][$key]=$row['count'];
			}
		}
		
		$joined=array_merge($joined,array());
		
		$category=array_sub($joined,'team_name');
		
		$series=array(
			array('name'=>'签约案件','data'=>array_sub($joined,'签约案件',NULL,true),'stack'=>0),
			array('name'=>'面谈咨询','data'=>array_sub($joined,'面谈咨询',NULL,true),'stack'=>1),
			array('name'=>'电话咨询','data'=>array_sub($joined,'电话咨询',NULL,true),'stack'=>2),
			array('name'=>'网络咨询','data'=>array_sub($joined,'网络咨询',NULL,true),'stack'=>3)
		);
		
		$this->load->addViewData('category_count', json_encode($category));
		$this->load->addViewData('series_count', json_encode($series,JSON_NUMERIC_CHECK));
		
		$this->load->view('achievement/team');
		$this->load->view('achievement/sidebar',true,'sidebar');
	}

	function staff(){
		
		$this->section_title='个人业绩';
		
		$this->config->set_user_item('date/from', $this->date->year_begin,false);
		
		if($this->input->post('date_from')){
			$this->config->set_user_item('date/from', $this->input->post('date_from'));
		}
		
		if($this->input->post('date_to')){
			$this->config->set_user_item('date/to', $this->input->post('date_to'));
		}
		
		//获得个人业绩金额
		$data=array(
			'办案新增创收'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'people',
				'role'=>array('主办律师'),
				'received'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'contract_date'=>array('from'=>$this->date->year_begin),
				'orderby'=>'sum desc'
			)),
			
			'办案存量创收'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'people',
				'role'=>array('主办律师'),
				'received'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'contract_date'=>array('to'=>$this->date->last_year_end),
			)),
			
			'所内接洽创收'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'people',
				'role'=>array('接洽律师'),
				'project_labels'=>array('所内案源'),
				'received'=>true,
				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),

			'个人案源创收'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'people',
				'role'=>array('案源人'),
				'received'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),


		);
		
		$joined=array();
		
		foreach($data as $key => $array){
			foreach($array as $row){
				if(!isset($joined[$row['people']])){
					$joined[$row['people']]=array(
						'people_name'=>$row['people_name'],
					);
				}
				$joined[$row['people']][$key]=$row['sum'];
			}
		}
		
		$joined=array_merge($joined,array());
		
		$category=array_sub($joined,'people_name');
		
		$series=array(
			array('name'=>'办案新增创收','data'=>array_sub($joined,'办案新增创收',NULL,true),'stack'=>2),
			array('name'=>'办案存量创收','data'=>array_sub($joined,'办案存量创收',NULL,true),'stack'=>2),
			array('name'=>'所内接洽创收','data'=>array_sub($joined,'所内接洽创收',NULL,true),'stack'=>1),
			array('name'=>'个人案源创收','data'=>array_sub($joined,'个人案源创收',NULL,true),'stack'=>1),
		);
		
		$this->load->addViewData('category', json_encode($category));
		$this->load->addViewData('series', json_encode($series,JSON_NUMERIC_CHECK));
		
		//获得个人业绩数量
		$this->load->model('project_model','project');
		
		$data=array(
			
			'接洽签约'=>$this->project->getList(array(
				'count'=>true,
				'group'=>'people',
				'role'=>'接洽律师',
				'labels'=>array('案件','所内案源'),
				'time_contract'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'orderby'=>'count desc'
			)),
			
			'案源签约'=>$this->project->getList(array(
				'count'=>true,
				'group'=>'people',
				'role'=>'案源人',
				'labels'=>array('案件','个人案源'),
				'time_contract'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'orderby'=>'count desc'
			)),
			
			'面谈接洽'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('咨询','面谈'),
				'group'=>'people',
				'role'=>'接洽律师',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
			)),

			'电话接洽'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('咨询','电话'),
				'group'=>'people',
				'role'=>'接洽律师',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),

			'网络接洽'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('咨询','网络'),
				'group'=>'people',
				'role'=>'接洽律师',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			))
			
		);
		
		$joined=array();
		
		foreach($data as $key => $array){
			foreach($array as $row){
				if(!isset($joined[$row['people']])){
					$joined[$row['people']]=array(
						'people_name'=>$row['people_name'],
					);
				}
				$joined[$row['people']][$key]=$row['count'];
			}
		}
		
		$joined=array_merge($joined,array());
		
		$category=array_sub($joined,'people_name');
		
		$series=array(
			array('name'=>'接洽签约','data'=>array_sub($joined,'接洽签约',NULL,true),'stack'=>0),
			array('name'=>'案源签约','data'=>array_sub($joined,'案源签约',NULL,true),'stack'=>1),
			array('name'=>'面谈接洽','data'=>array_sub($joined,'面谈接洽',NULL,true),'stack'=>2),
			array('name'=>'电话接洽','data'=>array_sub($joined,'电话接洽',NULL,true),'stack'=>3),
			array('name'=>'网络接洽','data'=>array_sub($joined,'网络接洽',NULL,true),'stack'=>4)
		);
		
		$this->load->addViewData('category_count', json_encode($category));
		$this->load->addViewData('series_count', json_encode($series,JSON_NUMERIC_CHECK));
		
		$this->load->view('achievement/staff');
		$this->load->view('achievement/sidebar',true,'sidebar');
	}

	function receivable($method=NULL){
		
	}
	
	function caseBonus(){
		
	}

	function teambonus(){
		
	}
	
	function index(){
		
		$this->load->model('account_model','account');
		
		$this->config->set_user_item('date/from', $this->date->year_begin,false);
		
		if($this->input->post('date_from')){
			$this->config->set_user_item('date/from', $this->input->post('date_from'));
		}
		
		if($this->input->post('date_to')){
			$this->config->set_user_item('date/to', $this->input->post('date_to'));
		}
		
		$data=array(
			'签约'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'month_contract',
				'received'=>false,
				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),
			
			'新增创收'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'month',
				'received'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'contract_date'=>array('from'=>$this->date->year_begin)
			)),
			
			'存量创收'=>$this->account->getList(array(
				'sum'=>true,
				'group'=>'month',
				'received'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'contract_date'=>array('to'=>$this->date->last_year_end),
			))
		);
		
		$joined=array();
		
		foreach($data as $key => $array){
			foreach($array as $row){
				if(!isset($joined[$row['month']])){
					$joined[$row['month']]=array(
						'month'=>$row['month'],
					);
				}
				$joined[$row['month']][$key]=$row['sum'];
			}
		}
		
		$joined=array_values($joined);
		
		$category=array_sub($joined,'month');
		
		$series=array(
			array('name'=>'新增创收','data'=>array_sub($joined,'新增创收',NULL,true)),
			array('name'=>'存量创收','data'=>array_sub($joined,'存量创收',NULL,true)),
			array('name'=>'签约','data'=>array_sub($joined,'签约',NULL,true)),
		);
		$this->load->addViewData('category', json_encode($category));
		$this->load->addViewData('series', json_encode($series,JSON_NUMERIC_CHECK));
		
		//总业绩表
		$summary=array(
			'_heading'=>array(
				'',
				'全所',
				'办案',
				'案源'
			),
			
			array(
				'签约',
				$this->account->getSum(array(
					'received'=>false,
					'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('主办律师','协办律师'),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('案源律师'),
					'ten_thousand_unit'=>true
				))
			),
			
			array(
				'预计',
				$this->account->getSum(array(
					'received'=>false,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('主办律师','协办律师'),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('案源律师'),
					'ten_thousand_unit'=>true
				))
			),
			
			array(
				'创收',
				$this->account->getSum(array(
					'received'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('主办律师','协办律师'),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('案源律师'),
					'ten_thousand_unit'=>true
				))
			)
			
		);
		
		$this->load->addViewData('summary', $summary);
		
		$this->load->view('achievement/summary');
		$this->load->view('achievement/sidebar',true,'sidebar');
		$this->load->view('achievement/summary_sidebar',true,'sidebar');
	}
	
	function client(){
		//TODO 新增客户统计
	}
}
?>