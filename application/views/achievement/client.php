<script type="text/javascript">
var chart_staffly_clients;
$(function(){
	chart_staffly_clients = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_staffly_clients',
			type:'column'
		},
		title: {
			text: '前两周新增客户'
		},
		xAxis: {
			categories: $.parseJSON('<?=$chart_staffly_clients_catogary?>')
		},
		series: $.parseJSON('<?=$chart_staffly_clients_series?>')
	}));
});
</script>
<div class="contentTableBox" style="width:100%">
	<?php arrayExportTable($client_collect_stat,NULL,false,false) ?>
	<div id="chart_staffly_clients" style="height:600px;margin:auto"></div>
</div>