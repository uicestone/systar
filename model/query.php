<?php
function query_fetch($id){
	$query="SELECT * FROM `case` WHERE id='".$id."'";
	return db_fetch_first($query,true);
}
?>