<html>
	<head>
		<title>My Form</title>
	</head>
	
	<body>
		<?php echo form_open('myform'); ?>
		
		<h5>Username</h5>
		<?php echo form_error('username'); ?>
		<input type="text" name="username" value="<?php echo set_value('username'); ?>" size="50" />
		
		<h5>Birthday</h5>
		<?php echo form_error('birthday'); ?>
		<input type="text" name="birthday" value="<?php echo set_value('birthday'); ?>" size="50" />
		
		<h5>ID_Number</h5>
		<?php echo form_error('id_number'); ?>
		<input type="text" name="id_number" value="<?php echo set_value('id_number'); ?>" size="50" />
		
		<div><input type="submit" value="Submit" /></div>
		
		</form>
	</body>
</html>