<?php
function express_fetch($id){
	$query="SELECT * FROM express WHERE id='".$id."'";
	return db_fetch_first($query,true);
}
?>