<?php
class Mail_model extends SS_Model{
	function __construct() {
		parent::__construct();
	}
	
	function getArticles($database,$article_ids){
		$db=$this->load->database($database,true);
		
		$articles=array();
		
		foreach($article_ids as $article_id){
			$query="SELECT aid,title,summary FROM portal_article_title WHERE aid = $article_id";
			$articles[]=$db->query($query)->row_array();
		}
		
		return $articles;

	}
}
?>
