<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" id="content">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
	<?=stylesheet('style/common')?>
	<?=stylesheet('style/jquery-ui/jquery-ui')?>
	<script type="text/javascript">
		var controller='<?=CONTROLLER?>';
		var affair='<?=@$this->user->permission[CONTROLLER]['_affair_name']?>';
		var action='<?=METHOD?>';
		var username='<?=$this->user->name?>';
		var sysname='<?=$this->company->sysname?>';
		var lastListAction='<?=$this->session->userdata('last_list_action')?>';
		var asPopupWindow=<?=intval($this->as_popup_window)?>;
	</script>
	<?=javascript('jquery')?>
	<?=javascript('jquery-ui')?>
	<?=javascript('common')?>
	<title></title>
</head>
<body style="background-image:url('/images/bg_<?=$this->company->syscode?>.gif')">
