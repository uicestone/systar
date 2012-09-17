<?php
function contact_fetch($id){
	$query="SELECT * FROM client WHERE id = '".$id."' AND classification IN ('相对方','联系人')";
	return db_fetch_first($query,true);
}
?>