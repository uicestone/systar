<?php
require_once '../PHPWord.php';

// New Word Document
$PHPWord = new PHPWord();

// New portrait section
$section = $PHPWord->createSection();

// Add text elements
$section->addText("大家好",array('bold'=>true));

$section->addText("hi");

// Save File
$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$objWriter->save('Text.docx');
?>