<? echo $search_bar;?>
<form method="post" name="createDir">
    <label>创建目录：</label><input type="text" name="dirName" size="10" />
    <input type="submit" name="createDirSubmit" value="创建" />
</form>


<form method="post" enctype="multipart/form-data">
    <input type="file" name="file" id="file" width="30" /><br>
    <label>备注：</label><input name="comment" type="text" size="10" />
    <input type="submit" name="fileSubmit" value="上传" />
</form>
<?php
$q="SELECT * FROM `document` WHERE id IN (SELECT file FROM document_fav WHERE uid='".$_SESSION['id']."') ORDER BY name";
$field=array(
	'checkbox'=>array('title'=>'<input type="submit" name="favDelete" value="删" />','orderby'=>false,'content'=>'<input type="checkbox" name="{id}" >','td_title'=>' width=40px'),
	'file'=>array('title'=>'收藏','content'=>'<a href="/document?view={id}">{name}</a>'));
$menu=array(
	'head'=>'<form method="post">',
	'foot'=>'</form>'
);
exportTable($q,$field,$menu,false,false);
?>