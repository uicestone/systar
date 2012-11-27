<?php
$config = array
			(
				array
				(
					'field' => 'username' ,
					'label' => 'Username' ,
					'rules' => 'required|is_unique(Client.name)'
				) ,
				array
				(
					'field' => 'birthday' ,
					'label' => 'Birthday' ,
					'rules' => 'required|callback_isDate'
				) ,
				array
				(
					'field' => 'id_number' ,
					'label' => 'ID_Number' ,
					'rules' => 'required|callback_verifyIdCard'
				)
			);
?>