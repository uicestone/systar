<?php
function company_fetchInfo(){
	$company_info=db_fetch_first("SELECT id AS company,name AS company_name,type AS company_type,syscode,sysname,ucenter,default_controller FROM company WHERE host='".$_SERVER['SERVER_NAME']."' OR syscode='".$_SERVER['SERVER_NAME']."'");
	if(is_array($company_info)){
		return $company_info;
	}else{
		return false;
	}
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
	global $_G;
	
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
			'total'=>achievementSum('contracted'),
			'my'=>achievementSum('contracted','my'),
			'contribute'=>achievementSum('contracted','contribute')
		),
		
		'estimated'=>array(
			'field'=>'预计',
			'total'=>achievementSum('estimated'),
			'my'=>achievementSum('estimated','my'),
			'contribute'=>achievementSum('estimated','contribute')
		),
		
		'collected'=>array(
			'field'=>'到账',
			'total'=>achievementSum('collected'),
			'my'=>achievementSum('collected','my'),
			'contribute'=>achievementSum('collected','contribute')
		)
	);
	
	$month_start_timestamp=strtotime(date('Y-m',$_G['timestamp']).'-1');
	$month_end_timestamp=mktime(0,0,0,date('m',$_G['timestamp'])+1,1,date('Y',$_G['timestamp']));
	
	$sidebar_table[]=array(
		'_field'=>array(
			'field'=>'本月',
			'total'=>'全所',
			'my'=>'主办',
			'contribute'=>'贡献'
		),
		
		'contracted'=>array(
			'field'=>'签约',
			'total'=>achievementSum('contracted','total',$month_start_timestamp),
			'my'=>achievementSum('contracted','my',$month_start_timestamp),
			'contribute'=>achievementSum('contracted','contribute',$month_start_timestamp)
		),
		
		'estimated'=>array(
			'field'=>'预计',
			'total'=>achievementSum('estimated','total',$month_start_timestamp,$month_end_timestamp),
			'my'=>achievementSum('estimated','my',$month_start_timestamp,$month_end_timestamp),
			'contribute'=>achievementSum('estimated','contribute',$month_start_timestamp,$month_end_timestamp)
		),
		
		'collected'=>array(
			'field'=>'到账',
			'total'=>achievementSum('collected','total',$month_start_timestamp),
			'my'=>achievementSum('collected','my',$month_start_timestamp),
			'contribute'=>achievementSum('collected','contribute',$month_start_timestamp)
		)
	);
	
	
	$recent_collect=achievementTodo('recent');
	$expired_collect=achievementTodo('expired');
	
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
?>