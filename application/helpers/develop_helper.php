<?php
function codeLines(){
	$dir='../';
	$src = APPPATH.'third_party/line-counter/';
	require $src . 'Folder.php';
	require $src . 'File.php';
	require $src . 'Option.php';
	require $src . 'Html.php';
	
	//Use GET so this script could be reused elsewhere
	//Set to user defined options or default one
	$options = array(
		'ignoreFolders' => explode(',','_doc,system,temp,config,errors,third_party,redmond,jQuery,api'),
		'ignoreFiles' => explode(',','jquery-ui.js,jquery.js,lunar.php'),
		'extensions' => explode(',','php,js,css')
	);
	
	//Scan user defined directory
	$folder = new Folder($dir, new Option($options));
	$folder->init();
	
	$lines = $folder->getLines();
	$whitespace = $folder->getWhitespace();
	$comments = $folder->getComments();
	
	return $lines.' lines';
}
?>
