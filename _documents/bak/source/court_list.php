<?php
if(!defined('IN_UICE'))
	exit('no permission');
	
$q="SELECT * 
	FROM `court` WHERE 1=1";

$search_form=processSearch($q,array('num'=>'案号','matter'=>'案由','plaintiff'=>'原告','defendant'=>'被告','court'=>'法院'));

processOrderby($q,'date');

$listLocator=processMultiPage($q);

$field=array(
	'checkbox'=>array('title'=>'','content'=>'<input type="checkbox" name="court[{id}]" >','td_title'=>' width=38px'),
	'court'=>'法院','room'=>'法庭','date'=>'开庭时间','num'=>'案号','matter'=>'案由',
	'depart'=>'部门','judge'=>'审判长',
	'plaintiff'=>array('title'=>'原告','eval'=>true,'content'=>"
		\$arrayPlaintiff=explode(',','{plaintiff}');
		\$return='';
		\$glue='';
		foreach(\$arrayPlaintiff as \$plaintiff){
			\$return.=\$glue;
			\$return.='<a href=\'/catologsale?add&name='.\$plaintiff.'&case={id}\' target=\'_blank\'>'.\$plaintiff.'</a>';
			\$glue='<br>';
		}
		return \$return;
	"),
	'defendant'=>array('title'=>'被告','eval'=>true,'content'=>"
		\$arrayDefendant=explode(',','{defendant}');
		\$return='';
		\$glue='';
		foreach(\$arrayDefendant as \$defendant){
			\$return.=\$glue;
			\$return.='<a href=\'/catologsale?add&name='.\$defendant.'&case={id}\' target=\'_blank\'>'.\$defendant.'</a>';
			\$glue='<br>';
		}
		return \$return;
	"),
	'status'=>array('title'=>'跟踪状态','eval'=>true,'content'=>'
		switch(\'{status}\'){
			case \'未处理\':return "<div style=\"background:#CFC;text-align:center\">未处理</div>";
			case \'已排除\':return "<div style=\"background:#FCC;text-align:center\">已排除</div>";
			case \'已跟踪\':return "<div style=\"background:#CCF;text-align:center\">已跟踪</span>";
		}
	')
);

$submitBar=array(
'head'=>'<div style="float:left;">'.
			'<input type="submit" name="follow" value="跟踪" />'.
			'<input type="submit" name="exclude" value="排除" />'.
			'<input type="submit" name="filter" value="筛选">'.
			'<input type="submit" name="followall" value="跟踪全部">'.
			(option('searchResult','true')?'<button type="button" value="searchCancel" onclick="redirectPara(this)">取消搜索</button>':'').
		'</div>'.
		'<div style="float:right;">'.
			$listLocator.
		'</div>',
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar,true);

require 'view/court_list_sidebar.php';
?>