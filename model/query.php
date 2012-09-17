<?php
function query_fetch($id){
	$query="SELECT * FROM query WHERE id='".$id."'";
	return db_fetch_first($query,true);
}
?>