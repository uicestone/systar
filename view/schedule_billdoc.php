<?php
require 'plugin/PHPWord/PHPWord.php';
model('document');

$PHPWord = new PHPWord();

$section = $PHPWord->createSection();

//$document = $PHPWord->loadTemplate('plugin/PHPWord/Examples/Template.docx');

$PHPWord->addTableStyle('schedule_billdoc',array('borderSize'=>1,'borderColor'=>'333','cellMargin'=>100));

$table = $section->addTable('schedule_billdoc');

foreach($table_array as $line_name=>$line){
	$table->addRow();
	foreach($line as $cell_name=>$cell){
		$table->addCell(1750)->addText(strip_tags($cell['html']));
	}
}

// Save File
$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$filename=iconv('utf-8','gbk',$_SESSION['username']).$_G['timestamp'];
$objWriter->save('temp/'.$filename);

//适应各浏览器的文件输出
$ua = $_SERVER["HTTP_USER_AGENT"];

$encoded_filename = urlencode(iconv('gbk','utf-8',$filename));
$encoded_filename = str_replace("+", "%20", $encoded_filename);

header('Content-Type:'.document_getMime('docx').';charset=utf-8');

if(preg_match("/MSIE/", $ua)) {
	header('Content-Disposition:filename="'.$encoded_filename.'"');
}elseif (preg_match("/Firefox/", $ua)) {
	header('Content-Disposition:filename*="utf8\'\''.$filename.'"');
}else {
	header('Content-Disposition:filename="'.$filename.'"');
}

$path='temp/'.$filename;

readfile($path);
unlink($path);
exit;
?>