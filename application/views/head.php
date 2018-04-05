<?=doctype('html5')?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
	<!--[if lt IE 9]><?=$this->javascript('html5')?><![endif]-->
	<?=$this->stylesheet('style/combined')?>
	<?=$this->javascript('combined')?>
	<link rel="icon" href="/images/favicon.ico" type="image/x-icon" />
	<title><?=$this->output->title.' '.$this->company->sysname?></title>
<?php if(isset($css) && $css){ ?>
	<style type="text/css">
		<?=$css?>
	</style>
<?php } ?>
</head>
<body style="background-image:url('/images/bg_<?=$this->company->syscode?>.gif')">
