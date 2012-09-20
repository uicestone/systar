<?php
if(!sessioned('currentPath',NULL,false))
	$_SESSION['document']['currentPath']=$_G['document_root'];

if(!sessioned('currentDir',NULL,false))
	$_SESSION['document']['currentDir']='root';
	
if(!sessioned('currentDirID',NULL,false))
	$_SESSION['document']['currentDirID']=1;
	
if(!sessioned('upID',NULL,false))
	$_SESSION['document']['upID']='';

$q="SELECT *
	FROM `document` 
	WHERE 1=1 ";

$search_bar=processSearch($q,array('name'=>'文件名'));

$q.=(option('in_search_mod')?'':"AND parent='".$_SESSION['document']['currentDirID']."'").'';

processOrderby($q,'type','ASC');
	
$listLocator=processMultiPage($q);

$field=option('in_search_mod')?
	array(
		'checkbox'=>array('title'=>'','content'=>'<input type="checkbox" name="document[{id}]" >','td_title'=>'width=38px'),
		'type'=>array(
			'title'=>'类型',
			'eval'=>true,
			'content'=>"
				if('{type}'==''){
					\$image='folder';
				}elseif(is_file('web/images/file_type/{type}.png')){
					\$image='{type}';
				}else{
					\$image='unknown';
				}
				return '<img src=\"images/file_type/'.\$image.'.png\" alt=\"{type}\" />';
			",
			'td_title'=>'width="70px"'
		),
		'name'=>array('title'=>'文件名','td_title'=>'width="150px"','surround'=>array('mark'=>'a','href'=>'/document?view={id}')),
		'path'=>'路径','comment'=>'备注'
	)
	:
	array(
		'checkbox'=>array('title'=>'','content'=>'<input type="checkbox" name="document[{id}]" >','td_title'=>' width=38px'),
		'type'=>array(
			'title'=>'类型',
			'eval'=>true,
			'content'=>"
				if('{type}'==''){
					\$image='folder';
				}elseif(is_file('images/file_type/{type}.png')){
					\$image='{type}';
				}else{
					\$image='unknown';
				}
				return '<img src=\"/images/file_type/'.\$image.'.png\" alt=\"{type}\" />';
			",
			'td_title'=>'width="55px"'
		),
		'name'=>array('title'=>'文件名','td_title'=>'width="150px"','surround'=>array('mark'=>'a','href'=>'/document?view={id}')),
		'username'=>array('title'=>'上传者','td_title'=>'width="70px"'),
		'comment'=>'备注'
	);

$menu=array(
	'head'=>'<div class="left">'.
				'<input type="submit" name="fav" value="收藏" />'.
				($_SESSION['document']['currentDirID']>1?"<button type='button' name='view' value='0' onclick='redirectPara(this)'>顶级</button><button type='button' name='view' value='".$_SESSION['document']['upID']."' onclick='redirectPara(this)'>上级</button>":'').
				(option('in_search_mod')?'':$_SESSION['document']['currentPath']).
			'</div>'.
			'<div class="right">'.
				$listLocator.
			'</div>',
);

exportTable($q,$field,$menu,true);
?>