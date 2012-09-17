<?php
function account_fetch($id){
	$query="SELECT * FROM account WHERE id='".$id."'";
	return db_fetch_first($query,true);
}
?>