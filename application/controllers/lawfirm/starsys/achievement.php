<?php
class Achievement extends SS_controller{
	
	var $list_args=array(
		'case_name'=>array('heading'=>array('data'=>'案件','width'=>'25%'),'cell'=>'<a href="#cases/{case}" class="right" style="margin-left:10px;">查看</a>{case_name}'),
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
		
		$this->output->title='小组业绩';
		
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
				'group_by'=>'team',
				'received'=>true,
				'count'=>true,
				'contract_date'=>array('from'=>$this->date->year_begin),
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'project_labels'=>array('费用已锁定'),
				'orderby'=>'sum desc'
			)),

			'存量创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'team',
				'received'=>true,
				'count'=>true,
				'contract_date'=>array('to'=>$this->date->last_year_end),
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'project_labels'=>array('费用已锁定')
			)),

			'签约'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'team',
				'received'=>false,
				'count'=>true,
 				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'project_labels'=>array('费用已锁定')
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
				'group_by'=>'team',
				'type'=>'cases',
				'time_contract'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'orderby'=>'count desc'
			)),
			
			'面谈咨询'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('面谈'),
				'type'=>'query',
				'group_by'=>'team',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
			)),

			'电话咨询'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('电话'),
				'type'=>'query',
				'group_by'=>'team',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),

			'网络咨询'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('网络'),
				'type'=>'query',
				'group_by'=>'team',
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
		
		$this->output->title='个人业绩';
		
		$this->config->set_user_item('date/from', $this->date->year_begin,false);
		
		if($this->input->post('date_from')){
			$this->config->set_user_item('date/from', $this->input->post('date_from'));
		}
		
		if($this->input->post('date_to')){
			$this->config->set_user_item('date/to', $this->input->post('date_to'));
		}
		
		//获得个人业绩金额
		$data=array(
			'主办新增创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('主办律师'),
				'received'=>true,
				'count'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'contract_date'=>array('from'=>$this->date->year_begin),
				'project_labels'=>array('费用已锁定'),
				'order_by'=>'sum desc'
			)),
			
			'主办存量创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('主办律师'),
				'received'=>true,
				'count'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'contract_date'=>array('to'=>$this->date->last_year_end),
				'project_labels'=>array('费用已锁定')
			)),
			
			'所内接洽创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('接洽律师'),
				'project_labels'=>array('所内案源','费用已锁定'),
				'received'=>true,
				'count'=>true,
				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
			)),

			'个人案源创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('案源人'),
				'received'=>true,
				'count'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'project_labels'=>array('费用已锁定')
			)),

			'主办签约'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('主办律师'),
				'received'=>false,
				'count'=>true,
				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'project_labels'=>array('费用已锁定')
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
				$joined[$row['people']][$key]=$row['sum'];
			}
		}
		
		$joined=array_merge($joined,array());
		
		$category=array_sub($joined,'people_name');
		
		$series=array(
			array('name'=>'主办新增创收','data'=>array_sub($joined,'主办新增创收',NULL,true),'stack'=>2),
			array('name'=>'主办存量创收','data'=>array_sub($joined,'主办存量创收',NULL,true),'stack'=>2),
			array('name'=>'所内接洽创收','data'=>array_sub($joined,'所内接洽创收',NULL,true),'stack'=>1),
			array('name'=>'个人案源创收','data'=>array_sub($joined,'个人案源创收',NULL,true),'stack'=>1),
			array('name'=>'主办签约','data'=>array_sub($joined,'主办签约',NULL,true),'stack'=>0)
		);
		
		$this->load->addViewData('category', json_encode($category));
		$this->load->addViewData('series', json_encode($series,JSON_NUMERIC_CHECK));
		
		//获得个人业绩数量
		$this->load->model('project_model','project');
		
		$data=array(
			
			'接洽签约'=>$this->project->getList(array(
				'count'=>true,
				'group_by'=>'people',
				'role'=>'接洽律师',
				'labels'=>array('所内案源'),
				'type'=>'cases',
				'time_contract'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'orderby'=>'count desc'
			)),
			
			'案源签约'=>$this->project->getList(array(
				'count'=>true,
				'group_by'=>'people',
				'role'=>'案源人',
				'labels'=>array('个人案源'),
				'type'=>'cases',
				'time_contract'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'orderby'=>'count desc'
			)),
			
			'面谈接洽'=>$this->project->getList(array(
				'count'=>true,
				'labels'=>array('面谈'),
				'group_by'=>'people',
				'role'=>'接洽律师',
				'first_contact'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
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
			array('name'=>'面谈接洽','data'=>array_sub($joined,'面谈接洽',NULL,true),'stack'=>2)
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
		
		//获得个人业绩金额
		$data=array(
			'主办新增创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('主办律师'),
				'received'=>true,
				'count'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'contract_date'=>array('from'=>$this->date->year_begin),
				'project_labels'=>array('费用已锁定'),
				'order_by'=>'sum desc'
			)),
			
			'主办存量创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('主办律师'),
				'received'=>true,
				'count'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'project_labels'=>array('费用已锁定'),
				'contract_date'=>array('to'=>$this->date->last_year_end)
			)),
			
			'所内接洽创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('接洽律师'),
				'project_labels'=>array('所内案源','费用已锁定'),
				'received'=>true,
				'count'=>true,
				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),

			'个人案源创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('案源人'),
				'project_labels'=>array('费用已锁定'),
				'received'=>true,
				'count'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),

			'主办签约'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'role'=>array('主办律师'),
				'project_labels'=>array('费用已锁定'),
				'received'=>false,
				'count'=>true,
				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
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
				$joined[$row['people']][$key]=$row['sum'];
			}
		}
		
		$joined=array_merge($joined,array());
		
		$this->load->addViewData('table', $this->table->setFields(array(
			'people_name'=>array('heading'=>'人员'),
			'主办新增创收'=>array('heading'=>'主办新增创收'),
			'主办存量创收'=>array('heading'=>'主办存量创收'),
			'所内接洽创收'=>array('heading'=>'所内接洽创收'),
			'个人案源创收'=>array('heading'=>'个人案源创收'),
			'主办签约'=>array('heading'=>'主办签约')
		))->generate($joined));
		
		$data=array(
			'签约'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'month_contract',
				'received'=>false,
				'count'=>true,
				'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to'))
			)),
			
			'新增创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'month',
				'received'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'count'=>true,
				'contract_date'=>array('from'=>$this->date->year_begin)
			)),
			
			'存量创收'=>$this->account->getList(array(
				'sum'=>true,
				'group_by'=>'month',
				'received'=>true,
				'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
				'count'=>true,
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
				'主办',
				'案源'
			),
			
			array(
				'签约',
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('主办律师'),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'contract_date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('案源人'),
					'ten_thousand_unit'=>true
				))
			),
			
			array(
				'预计',
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('主办律师'),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('案源人'),
					'ten_thousand_unit'=>true
				))
			),
			
			array(
				'创收',
				$this->account->getSum(array(
					'received'=>true,
					'count'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'ten_thousand_unit'=>true,
					'project_labels'=>array('费用已锁定')
				)),
				$this->account->getSum(array(
					'received'=>true,
					'count'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('主办律师'),
					'ten_thousand_unit'=>true,
					'project_labels'=>array('费用已锁定')
				)),
				$this->account->getSum(array(
					'received'=>true,
					'count'=>true,
					'date'=>array('from'=>$this->config->user_item('date/from'),'to'=>$this->config->user_item('date/to')),
					'people'=>$this->user->id,
					'role'=>array('案源人'),
					'ten_thousand_unit'=>true,
					'project_labels'=>array('费用已锁定')
				))
			)
			
		);
		
		$this->load->addViewData('summary', $summary);
		
		//月业绩表
		$summary_monthly=array(
			'_heading'=>array(
				'本月',
				'全所',
				'主办',
				'案源'
			),
			
			array(
				'签约',
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'contract_date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'contract_date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'people'=>$this->user->id,
					'role'=>array('主办律师'),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'contract_date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'people'=>$this->user->id,
					'role'=>array('案源人'),
					'ten_thousand_unit'=>true
				))
			),
			
			array(
				'预计',
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'people'=>$this->user->id,
					'role'=>array('主办律师'),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>false,
					'count'=>true,
					'date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'people'=>$this->user->id,
					'role'=>array('案源人'),
					'ten_thousand_unit'=>true
				))
			),
			
			array(
				'创收',
				$this->account->getSum(array(
					'received'=>true,
					'count'=>true,
					'date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>true,
					'count'=>true,
					'date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'people'=>$this->user->id,
					'role'=>array('主办律师'),
					'ten_thousand_unit'=>true
				)),
				$this->account->getSum(array(
					'received'=>true,
					'count'=>true,
					'date'=>array('from'=>$this->date->month_begin,'to'=>$this->date->month_end),
					'people'=>$this->user->id,
					'role'=>array('案源人'),
					'ten_thousand_unit'=>true
				))
			)
			
		);
		
		$this->load->addViewData('summary_monthly', $summary_monthly);
		
		$this->load->view('achievement/summary');
		$this->load->view('achievement/sidebar',true,'sidebar');
		$this->load->view('achievement/summary_sidebar',true,'sidebar');
	}
	
	function client(){
		//TODO 新增客户统计
	}
	
	function generateBonus(){
		
		$staff_bonused=array();
		$staff_contribute=array();
		
		//当年所有到账
		$received_accounts=$this->account->getList(array(
			'date'=>array(
				'from'=>'2013-01-01'
			),
			'count'=>true,
			'received'=>true,
			'get_labels'=>true,
			'order_by'=>'date'
		));
		
		//累进算法
		function progressiveBonus($contribute){
			return
				($contribute-1E6>0?$contribute-1E6:0)*0.4
				+(($contribute>1E6?1E6:$contribute)-5E5>0?($contribute>1E6?1E6:$contribute)-5E5:0)*0.35
				+(($contribute>5E5?5E5:$contribute)-3E5>0?($contribute>5E5?5E5:$contribute)-3E5:0)*0.25
				+(($contribute>3E5?3E5:$contribute)-1E5>0?($contribute>3E5?3E5:$contribute)-1E5:0)*0.15;
		}
		
		foreach($received_accounts as $received_account){
			if(!$received_account['project']){
				continue;
			}
			
			//如果该笔创收是退款，而且去年到过钱，则不计入创收，不计算奖金
			if($this->account->getList(array(
				'date'=>array(
					'to'=>'2012-12-31'
				),
				'count'=>true,
				'received'=>true,
				'account'=>$received_account['account']
			)) && $received_account['amount']<0){
				continue;
			}

			$staffs=$this->people->getList(array('in_project'=>$received_account['project'],'project_people_role'=>'主办律师'));
			
			//对于每一笔到账，列出其案件下的主办律师
			foreach($staffs as $staff){
				!isset($staff_contribute[$staff['id']]) && $staff_contribute[$staff['id']]=0;
				!isset($staff_bonused[$staff['id']]) && $staff_bonused[$staff['id']]=0;
				
				//该员工本年总贡献
				$staff_contribute[$staff['id']]+=$received_account['amount']*$staff['weight'];
				
				//该员工本年总奖金
				$sum_bonus=progressiveBonus($staff_contribute[$staff['id']]);
				
				//本次应发奖金=总奖金-已发
				$bonus=$sum_bonus-$staff_bonused[$staff['id']];
				
				if(in_array('奖金已生成',$received_account['labels'])){
					continue;
				}
				
				$id=$this->account->add(array(
					'name'=>'办案奖金',
					'type'=>'办案奖金',
					'people'=>$staff['id'],
					'amount'=>-$bonus*0.65,
					'account'=>$received_account['account'],
					'received'=>false,
					'count'=>false,
					'date'=>$received_account['date'],
					'project'=>$received_account['project'],
					'display'=>true,
					'comment'=>'本笔创收：'.$received_account['amount']
						.'，主办比例'.(round($staff['weight']*100,1)).'%'
						.'，当年已贡献：'.$staff_contribute[$staff['id']]
						.'，合计办案奖金：'.$sum_bonus
						.'，已计：'.$staff_bonused[$staff['id']]
						.'，补计：'.$bonus
						.'，立即发放65%（'.round($bonus*0.65,2).'元）'
				));
				
				$id=$this->account->add(array(
					'name'=>'结案奖金储备',
					'type'=>'结案奖金储备',
					'amount'=>-$bonus*0.35,
					'account'=>$received_account['account'],
					'count'=>false,
					'received'=>false,
					'project'=>$received_account['project'],
					'display'=>true,
					'comment'=>'本笔创收：'.$received_account['amount']
						.'，主办比例'.(round($staff['weight']*100,1)).'%'
						.'，当年已贡献：'.$staff_contribute[$staff['id']]
						.'，合计办案奖金：'.$sum_bonus
						.'，已计：'.$staff_bonused[$staff['id']]
						.'，补计：'.$bonus
						.'，缓发35%（'.round($bonus*0.35,2).'元）作为结案奖金储备'
				));
				
				//保存已发奖金
				$staff_bonused[$staff['id']]=$sum_bonus;
			}
			
			$this->account->addLabel($received_account['id'], '奖金已生成');
			
		}
		
	}
	
	function generateArchiveBonus(){
		
		$this->load->model('cases_model','cases');
		
		$archived_cases=$this->cases->getList(array(
			'end'=>array('from'=>'2013-01-01','to'=>'2013-06-30'),
			'active'=>false,
			'without_labels'=>array('结案奖金已生成')
		));
		
		foreach($archived_cases as $archived_case){
			$archived_accounts=$this->account->getList(array(
				'type'=>'结案奖金储备',
				'project'=>$archived_case['id']
			));
			
			if(!$archived_accounts){
				continue;
			}
			
			$archived_bonus=array_sum(array_sub($archived_accounts,'amount'));
			
			$staffs=$this->people->getList(array(
				'in_project'=>$archived_case['id'],
				'project_people_role'=>'实际贡献'
			));
			
			foreach($staffs as $staff){
				$this->account->add(array(
					'name'=>'结案奖金',
					'type'=>'结案奖金',
					'amount'=>$archived_bonus*$staff['weight'],
					'received'=>false,
					'count'=>false,
					'project'=>$archived_case['id'],
					'people'=>$staff['id'],
					'display'=>true,
					'comment'=>"结案奖金储备：\n".implode("\n",array_sub($archived_accounts,'comment'))
						.' 实际贡献：'.round($staff['weight']*100,1).'%'
				));
			}
			
			$this->cases->addLabel($archived_case['id'], '结案奖金已生成');
		}
	}
}
?>