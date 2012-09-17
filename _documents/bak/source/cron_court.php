<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
if(!isset($_SESSION['cron']['court']['do']))
	$_SESSION['cron']['court']['do']=true;

if(!isset($_SESSION['cron']['court']['page']))
	$_SESSION['cron']['court']['page']=1;

if(!isset($_SESSION['cron']['court']['time']))
	$_SESSION['cron']['court']['time']=time();



//while($_SESSION['cron']['court']['do']){
	echo $url="http://www.hshfy.sh.cn/shfy/gweb/ktgg_search.jsp?ktrqks=".date('Y-m-d',time())."&ktrqjs=2015-1-1&pagesnum=".$_SESSION['cron']['court']['page'];
	$content = file_get_contents($url);
	preg_match('/<TABLE\sid=report[\s\S]*?<TBODY>([\s\S]*?)<\/TBODY>[\s\S]*?<\/TABLE>/i',$content,$matches);
	$content=$matches[1];
	$content=preg_replace('/<TR.*?>/','<TR>',$content);
	$content=preg_replace('/<TD.*?>/','<TD>',$content);
	$content=preg_replace('/<.*?FONT.*?>/','',$content);
	$content=preg_replace('/<.*?div.*?>/','',$content);
	$content=preg_replace('/&nbsp;/','',$content);
	$content=preg_replace('/\s/','',$content);
	
	$content=iconv('gbk','utf-8',$content);
	
	$data=preg_split('/<\/TR><TR>/',$content);
	
	for($i=1;$i<count($data);$i++){
		$data[$i]=preg_split('/<\/TD><TD>|<\/TD>/',$data[$i]);
		unset($data[$i][11]);
		for($j=0;$j<11;$j++){
			switch($j){
				case 0:$data[$i]['court']=$data[$i][$j];unset($data[$i][$j]);break;
				case 1:$data[$i]['room']=$data[$i][$j];unset($data[$i][$j]);break;
				case 2:$data[$i]['date']=$data[$i][$j];unset($data[$i][$j]);break;
				case 3:$data[$i]['num']=$data[$i][$j];unset($data[$i][$j]);break;
				case 4:$data[$i]['matter']=$data[$i][$j];unset($data[$i][$j]);break;
				case 5:$data[$i]['depart']=$data[$i][$j];unset($data[$i][$j]);break;
				case 6:$data[$i]['judge']=$data[$i][$j];unset($data[$i][$j]);break;
				case 7:$data[$i]['plaintiff']=$data[$i][$j];unset($data[$i][$j]);break;
				case 8:$data[$i]['defendant']=$data[$i][$j];unset($data[$i][$j]);break;
				case 9:$data[$i]['open']=$data[$i][$j];unset($data[$i][$j]);break;
				case 10:$data[$i]['on_court']=$data[$i][$j];unset($data[$i][$j]);break;
			}
		}
		$data[$i]['time']=$_SESSION['cron']['court']['time'];
		db_insert('court',$data[$i]);
	}
	//print_r($data);

	unset($data[0]);
	
	echo '<br />'.$_SESSION['cron']['court']['page'].' page saved<br>';
	
	$_SESSION['cron']['court']['page']++;
	
	if(!isset($data[1])){
		$_SESSION['cron']['court']['do']=false;
		unset($_SESSION['cron']['court']['do']);
		unset($_SESSION['cron']['court']['page']);
		$q="UPDATE cron SET lastrun = '".time()."' WHERE name = 'courtUpdate'";
		mysql_query($q,$db_link);
	}

	if($_SESSION['cron']['court']['do'])
		redirect('cron?script=court','js');
//}
?>