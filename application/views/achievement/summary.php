<? javascript('highcharts/highcharts') ?>
<script type="text/javascript">
var chart;
$(function(){
	chart = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart'
		},
		title: {
			text: '业绩概览'
		},
		xAxis: {
			categories: $.parseJSON('<? echo $months?>')
		},
		series: $.parseJSON('<? echo $series ?>')
	}));
});
</script>
<div class="contentTableBox" style="width:100%">
	<div id="chart" style="height:600px;margin:auto"></div>
</div>
