<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" id="content">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
	<? stylesheet('style/common')?>
	<? stylesheet('style/redmond/jquery-ui-1.8.21')?>
	<? javascript('jquery')?>
	<? javascript('jquery-ui')?>
	<? stylesheet('js/qtip2/jquery.qtip.min')?>
	<? javascript('qtip2/jquery.qtip.min')?>
	<? javascript('common')?>
	<script type="text/javascript">
		var controller='<? echo CONTROLLER?>';
		var affair='<? echo array_dir('_SESSION/permission/'.CONTROLLER.'/_affair_name')?>';
		var action='<? echo $this->uri->segment(2)?>';
		var username='<? echo array_dir('_SESSION/username')?>';
		var sysname='<? echo $this->config->item('sysname')?>';
	</script>
	<? javascript('contentframe')?>
</head>
<body id="content" style="background-image:url('/images/bg_<? echo $this->config->item('syscode')?>.gif') ">
