<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
	<!--[if lt IE 9]><?=$this->javascript('html5')?><![endif]-->
	<?=$this->stylesheet('css/combined')?>
	<link rel="icon" href="/images/favicon.ico" type="image/x-icon" />
	<title><?=$this->output->title.' '.$this->company->sysname?></title>
<?if(isset($css) && $css){?>
	<style type="text/css">
		<?=$css?>
	</style>
<?}?>
</head>
<body>