<?php
require 'plugin/PHPWord/PHPWord.php';

$PHPWord = new PHPWord();

$section = $PHPWord->createSection();

$PHPWord->addTableStyle('schedule_billdoc',array('borderSize'=>1,'borderColor'=>'333','cellMargin'=>100));

$table = $section->addTable('schedule_billdoc');

foreach($document_catalog as $doctype){
	$table->addRow();
	$table->addCell(3000)->addText($doctype);
	$table->addCell(9000);
}

// Save File
$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$filename=$_SESSION['username'].$_G['timestamp'].'.docx';

$path=iconv('utf-8','gbk','temp/'.$filename);

$objWriter->save($path);

document_exportHead($filename);

readfile($path);
unlink($path);
exit;
?>