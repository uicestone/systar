<?php
class Company_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id=NULL){
		$query="
			SELECT id,name,type,syscode,sysname,ucenter,default_controller
			FROM company 
			WHERE host='{$this->input->server('SERVER_NAME')}' OR syscode='{$this->input->server('SERVER_NAME')}'";
		
		return $this->db->query($query)->row_array();
	}

	function starsys_schedule_side_table(){
		$sidebar_table=array();
		$sidebar_table[]=array(
			'_heading'=>array(
				'field'=>'本年',
				'total'=>'全所',
				'my'=>'主办',
				'contribute'=>'贡献'
			),
			
			'contracted'=>array(
				'field'=>'签约',
				'total'=>$this->achievement->sum('contracted'),
				'my'=>$this->achievement->sum('contracted','my'),
				'contribute'=>$this->achievement->sum('contracted','contribute')
			),
			
			'estimated'=>array(
				'field'=>'预计',
				'total'=>$this->achievement->sum('estimated'),
				'my'=>$this->achievement->sum('estimated','my'),
				'contribute'=>$this->achievement->sum('estimated','contribute')
			),
			
			'collected'=>array(
				'field'=>'到账',
				'total'=>$this->achievement->sum('collected'),
				'my'=>$this->achievement->sum('collected','my'),
				'contribute'=>$this->achievement->sum('collected','contribute')
			)
		);
		
		$month_start_timestamp=strtotime(date('Y-m',$this->config->item('timestamp')).'-1');
		$month_end_timestamp=mktime(0,0,0,date('m',$this->config->item('timestamp'))+1,1,date('Y',$this->config->item('timestamp')));
		
		$sidebar_table[]=array(
			'_heading'=>array(
				'本月',
				'全所',
				'主办',
				'贡献'
			),
			
			'contracted'=>array(
				'签约',
				$this->achievement->sum('contracted','total',$month_start_timestamp),
				$this->achievement->sum('contracted','my',$month_start_timestamp),
				$this->achievement->sum('contracted','contribute',$month_start_timestamp)
			),
			
			'estimated'=>array(
				'预计',
				$this->achievement->sum('estimated','total',$month_start_timestamp,$month_end_timestamp),
				$this->achievement->sum('estimated','my',$month_start_timestamp,$month_end_timestamp),
				$this->achievement->sum('estimated','contribute',$month_start_timestamp,$month_end_timestamp)
			),
			
			'collected'=>array(
				'到账',
				$this->achievement->sum('collected','total',$month_start_timestamp),
				$this->achievement->sum('collected','my',$month_start_timestamp),
				$this->achievement->sum('collected','contribute',$month_start_timestamp)
			)
		);
		
		
		$recent_collect=$this->achievement->todo('recent');
		$expired_collect=$this->achievement->todo('expired');
		
		$sidebar_table[]=array(
			'_heading'=>array(
				'近期催收',
				'到期未收'
			),
			array(
				'<a href="achievement/recent">'.$recent_collect['num'].'('.$recent_collect['sum'].')'.'</a>',
				'<a href="achievement/expired">'.$expired_collect['num'].'('.$expired_collect['sum'].')'.'</a>'
			)
		);
		
		if($this->user->isLogged('manager')){
			$staff=$this->input->get('staff')?$this->input->get('staff'):false;
			$sidebar_table[]=array(
				'_heading'=>array(
					'schedule_check'=>'员工日程检阅'
				),
				array(
					'schedule_check'=>'<select name="staff" class="filter" method="get">'
						.html_option(false,$staff,true,'staff',NULL,'name',"id IN (SELECT staff FROM manager_staff WHERE manager={$this->user->id}) AND position IS NOT NULL")
						.'</select>'
				)
			);
		}
		
		return $sidebar_table;
	}
}
?>