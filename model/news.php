<?php
function news_fetch($id){
	$query="SELECT * FROM news WHERE id = '".$id."'";
	return db_fetch_first($query,true);
}
?>