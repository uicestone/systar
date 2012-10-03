<?php
class Document_model extends CI_Model{
	function __construct(){
		parent::__construct();
		require 'class/filetype.php';
	}
	
	function document_getMime($file_extension){
		$file_extension=strtolower($file_extension);
		$filetype=new Filetype();
		return $filetype->type[$file_extension];
	}
	
	function document_openInBrowser($file_extension){
		$file_extension=strtolower($file_extension);
		$filetype=new Filetype();
		if(in_array($file_extension,$filetype->open_in_browser)){
			return true;
		}else{
			return false;
		}
	}
	
	function document_getExtension($filename){
		if(preg_match('/\.(\w*?)$/',$filename,$extname_match)){
			return $extname_match[1];
		}else{
			return '';
		}
	}
	
	function document_exportHead($filename){
		$ua = $_SERVER["HTTP_USER_AGENT"];
		
		$encoded_filename = urlencode($filename);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);
		
		$extension=document_getExtension($filename);
		
		header('Content-Type: '.document_getMime($extension).';charset=utf-8');
		
		if (preg_match("/MSIE/", $ua)) {
			header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
		}else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		}else {
			header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
	}
}
?>