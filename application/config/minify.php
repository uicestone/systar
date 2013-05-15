<?php
$config['minify_source']['js/combined.js']=array(
	'js/underscore-min.js',
	ENVIRONMENT==='development'?'js/jQuery/jquery-1.9.1.min.js':'js/jQuery/jquery-1.9.1.js',
	'js/jQuery/jquery-migrate-1.1.1.js',
	'js/backbone-min.js',
	'js/jQuery/jquery-ui-1.10.3.custom.min.js',

	'js/jQuery/jquery.placeholder.js',
	'js/jQuery/jQueryRotate.2.2.js',
	'js/jQuery/jquery-ui.etc.js',
	'js/jQuery/fullcalendar/fullcalendar.js',
	'js/jQuery/highcharts/highcharts.js',
	'js/jQuery/select2/select2.js',
	'js/jQuery/select2/select2_locale_zh-CN.js',
	'js/jQuery/jquery.iframe-transport.js',
	'js/jQuery/jquery.fileupload.js',
	'js/jQuery/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon.js',

	'js/router.js',

	'js/functions.js',
	'js/events.js',

	'js/schedule.js',
	'js/schedule_widget.js',
	'js/schedule_calendar.js',
	'js/message.js'
);

$config['minify_source']['style/combined.css']=array(
	'style/redmond/jquery-ui-1.10.3.custom.css',
	'style/icomoon/style.css',
	'js/jQuery/fullcalendar/fullcalendar.css',
	'js/jQuery/select2/select2.css',
	'js/jQuery/jQuery-Timepicker-Addon/jquery-ui-timepicker-addon.css',
	'style/common.css'
);
?>
