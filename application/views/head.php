<?=doctype('html5')?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
	<!--[if lt IE 9]><?=javascript('html5')?><![endif]-->
	
	<?=stylesheet('style/redmond/jquery-ui-1.10.2.custom')?>
	<?=stylesheet('style/icomoon/style')?>
	
	<?=javascript('jQuery/jquery-1.7.2.min')?>
	<?=javascript('jQuery/jquery-ui-1.10.2.custom.min')?>

	<?=javascript('jQuery/jquery.placeholder')?>
	<?=javascript('jQuery/jQueryRotate.2.2')?>
	<?=javascript('jQuery/jquery.hashchange-us')?>
	<?=javascript('jQuery/jquery-ui.etc')?>

	<?=stylesheet('js/jQuery/fullcalendar/fullcalendar')?>
	<?=javascript('jQuery/fullcalendar/fullcalendar')?>
	
	<?=javascript('jQuery/highcharts/highcharts')?>
	
	<?=stylesheet('js/jQuery/select2/select2')?>
	<?=javascript('jQuery/select2/select2')?>
	<?=javascript('jQuery/select2/select2_locale_zh-CN')?>
	
	<?=javascript('jQuery/jquery.iframe-transport')?>
	<?=javascript('jQuery/jquery.fileupload')?>
	
	<?=javascript('jQuery/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon')?>
	<?=stylesheet('js/jQuery/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon')?>

	<?=javascript('schedule')?>

	<?=stylesheet('style/common')?>
	<?=javascript('functions')?>
	<?=javascript('events')?>

	<link rel="icon" href="/images/favicon.ico" type="image/x-icon" />
	<title><?=$this->company->sysname?></title>
</head>
<body style="background-image:url('/images/bg_<?=$this->company->syscode?>.gif')">
