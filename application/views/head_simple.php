<?=doctype('html5')?>

<html xmlns="http://www.w3.org/1999/xhtml" id="content">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
	<!--[if lt IE 9]><?=$this->javascript('html5')?><![endif]-->
	<?=$this->stylesheet('style/redmond/jquery-ui-1.10.3.custom')?>
	<?=$this->stylesheet('style/common')?>
	
	<?=$this->javascript('jQuery/jquery-1.10.2.min')?>
	<?=$this->javascript('jQuery/jquery.placeholder')?>

	<link rel="icon" href="/images/favicon.ico" type="image/x-icon" />
	<title><?=$this->company->sysname?></title>
</head>
<body style="background-image:url('/images/bg_<?=$this->company->syscode?>.gif')">
