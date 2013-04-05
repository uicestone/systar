<?php
class Company_model extends BaseItem_model{
	
	var $name;
	var $type;
	var $host;
	var $syscode;
	var $sysname;
	var $ucenter;
	var $default_controller;
	
	function __construct(){
		parent::__construct();
		$this->table='company';
		$this->recognize($this->input->server('SERVER_NAME'));

		//获取存在数据库中的公司配置项
		$this->db->from('company_config')
			->where('company',$this->id);
		$this->config->company=array_sub($this->db->get()->result_array(),'value','name');
		
	}

	function recognize($host_name){
		$this->db->select('id,name,type,syscode,sysname,ucenter,default_controller')
			->from('company')
			->or_where(array('host'=>$host_name,'syscode'=>$host_name));

		$row_array=$this->db->get()->row_array();
		
		if(!$row_array){
			show_error("We're sorry but no company called $host_name here.");
		}
		
		foreach($row_array as $key => $value){
			$this->$key=$value;
		}
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
		
		$month_start_timestamp=strtotime(date('Y-m',$this->date->now).'-1');
		$month_end_timestamp=mktime(0,0,0,date('m',$this->date->now)+1,1,date('Y',$this->date->now));
		
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
		
		
		$recent_collect=$this->achievement->receivableSum('recent',NULL,date('Y-m-d',$this->date->now+86400*30));
		$expired_collect=$this->achievement->receivableSum('expired');
		
		$sidebar_table[]=array(
			'_heading'=>array(
				'近期催收',
				'到期未收'
			),
			array(
				'<a href="#achievement/receivable/recent">'.$recent_collect['num'].'('.$recent_collect['sum'].')'.'</a>',
				'<a href="#achievement/receivable/expired">'.$expired_collect['num'].'('.$expired_collect['sum'].')'.'</a>'
			)
		);
		
		return $sidebar_table;
	}
}
?>