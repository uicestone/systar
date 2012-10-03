<?php
class Company_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function school_init(){
		/*
			以8/1和农历新年作为学期的分界线
		*/
		global $_G;
		require 'class/lunar.php';
		$year=date('y',$_G['timestamp']);//两位数年份
		$term_begin_this_year_timestamp=strtotime($year.'-8-1');//今年8/1
		$lunar=new Lunar();
		$lunar_this_new_year_timestamp=$lunar->L2S($year.'-1-1');//今年农历新年的公历
		if($_G['timestamp']>=$term_begin_this_year_timestamp){
			$term=1;$term_year=$year;
		}elseif($_G['timestamp']<$lunar_this_new_year_timestamp){
			$term=1;$term_year=$year-1;
		}else{
			$term=2;$term_year=$year-1;
		}
		array_dir('_SESSION/global/current_term',$term_year.'-'.$term);//计算当前学期
		array_dir('_SESSION/global/highest_grade',$term_year-2);//计算当前在校最高年级
	}
	
	function starsys_schedule_side_table(){
		$CI=&get_instance();
		
		$sidebar_table=array();
		$sidebar_table[]=array(
			'_field'=>array(
				'field'=>'本年',
				'total'=>'全所',
				'my'=>'主办',
				'contribute'=>'贡献'
			),
			
			'contracted'=>array(
				'field'=>'签约',
				'total'=>$CI->achievement->sum('contracted'),
				'my'=>$CI->achievement->sum('contracted','my'),
				'contribute'=>$CI->achievement->sum('contracted','contribute')
			),
			
			'estimated'=>array(
				'field'=>'预计',
				'total'=>$CI->achievement->sum('estimated'),
				'my'=>$CI->achievement->sum('estimated','my'),
				'contribute'=>$CI->achievement->sum('estimated','contribute')
			),
			
			'collected'=>array(
				'field'=>'到账',
				'total'=>$CI->achievement->sum('collected'),
				'my'=>$CI->achievement->sum('collected','my'),
				'contribute'=>$CI->achievement->sum('collected','contribute')
			)
		);
		
		$month_start_timestamp=strtotime(date('Y-m',$CI->config->item('timestamp')).'-1');
		$month_end_timestamp=mktime(0,0,0,date('m',$CI->config->item('timestamp'))+1,1,date('Y',$CI->config->item('timestamp')));
		
		$sidebar_table[]=array(
			'_field'=>array(
				'field'=>'本月',
				'total'=>'全所',
				'my'=>'主办',
				'contribute'=>'贡献'
			),
			
			'contracted'=>array(
				'field'=>'签约',
				'total'=>$CI->achievement->sum('contracted','total',$month_start_timestamp),
				'my'=>$CI->achievement->sum('contracted','my',$month_start_timestamp),
				'contribute'=>$CI->achievement->sum('contracted','contribute',$month_start_timestamp)
			),
			
			'estimated'=>array(
				'field'=>'预计',
				'total'=>$CI->achievement->sum('estimated','total',$month_start_timestamp,$month_end_timestamp),
				'my'=>$CI->achievement->sum('estimated','my',$month_start_timestamp,$month_end_timestamp),
				'contribute'=>$CI->achievement->sum('estimated','contribute',$month_start_timestamp,$month_end_timestamp)
			),
			
			'collected'=>array(
				'field'=>'到账',
				'total'=>$CI->achievement->sum('collected','total',$month_start_timestamp),
				'my'=>$CI->achievement->sum('collected','my',$month_start_timestamp),
				'contribute'=>$CI->achievement->sum('collected','contribute',$month_start_timestamp)
			)
		);
		
		
		$recent_collect=$CI->achievement->todo('recent');
		$expired_collect=$CI->achievement->todo('expired');
		
		$sidebar_table[]=array(
			'_field'=>array(
				'近期催收',
				'到期未收'
			),
			array(
				'<a href="achievement?recent">'.$recent_collect['num'].'('.$recent_collect['sum'].')'.'</a>',
				'<a href="achievement?expired">'.$expired_collect['num'].'('.$expired_collect['sum'].')'.'</a>'
			)
		);
		
		if(is_logged('manager')){
			$staff=got('staff')?$_GET['staff']:false;
			$sidebar_table[]=array(
				'_field'=>array(
					'schedule_check'=>'员工日程检阅'
				),
				array(
					'schedule_check'=>'<select name="staff" class="filter" method="get">'
						.html_option(false,$staff,true,'staff',NULL,'name',"id IN (SELECT staff FROM manager_staff WHERE manager='".$_SESSION['id']."') AND position IS NOT NULL")
						.'</select>'
				)
			);
		}
		
		return $sidebar_table;
	}
}
?>