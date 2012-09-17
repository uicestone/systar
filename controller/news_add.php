<?php
getPostData();

//取得数据
$q_news="SELECT * FROM news WHERE id='".post(''.IN_UICE.'/id')."'";
$r_news=db_query($q_news);
if(db_rows($r_news)==0){
	showMessage('新闻不存在','warning');exit;
}
post('news',db_fetch_array($r_news));

$submitable=false;//可提交性，false则显示form，true则可以跳转

if(is_posted('submit')){
	$submitable=true;
	
	$_SESSION[IN_UICE]['post'][IN_UICE]=array_replace_recursive($_SESSION[IN_UICE]['post'][IN_UICE],array_trim($_POST[IN_UICE]));
	
	if(array_dir('_POST/'.IN_UICE.'/title')==''){
		$submitable=false;
		showMessage('请填写标题','warning');
	}
	
	processSubmit($submitable);
}
?>