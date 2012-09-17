<?php
model('document');

if(isset($_GET['document']))
	$id=$_GET['document'];
else
	exit('file id id not defined');

$q_case_document="SELECT * FROM case_document WHERE id='".$id."'";
$r_case_document=db_query($q_case_document);
$case_document=mysql_fetch_array($r_case_document);

//适应各浏览器的文件输出
$ua = $_SERVER["HTTP_USER_AGENT"];

$filename = $case_document['name'];
$encoded_filename = urlencode($filename);
$encoded_filename = str_replace("+", "%20", $encoded_filename);

if(document_openInBrowser($case_document['type'])){
	header('Content-Type:'.document_getMime($case_document['type']).';charset=utf-8');
}else{
	header('Content-Type:application/octet-stream;charset=utf-8');
	header('Content-Disposition:attachment');
}

if(preg_match("/MSIE/", $ua)) {
	header('Content-Disposition:filename="'.$encoded_filename.'"');
}else if (preg_match("/Firefox/", $ua)) {
	header('Content-Disposition:filename*="utf8\'\''.$filename.'"');
}else {
	header('Content-Disposition:filename="'.$filename.'"');
}

$path=iconv("utf-8","gbk",$_G['case_document_path'].'/'.$id);

readfile($path);
exit;
?>