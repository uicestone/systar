<?php
$file=db_fetch_first("SELECT * FROM document WHERE id = '".intval($_GET['view'])."'");

document_exportHead($file['name']);

$path=$file['path'];
$path=iconv("utf-8","gbk",$path);
readfile($path);
exit;
?>