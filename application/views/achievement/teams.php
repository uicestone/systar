<script type="text/javascript">
var chart;
$(function(){
	chart = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart',type: 'column'
		},
		title: {
			text: '小组业绩统计'
		},
		xAxis: {
			categories: <? echo $category?>
		},
		series: <? echo $series ?>
	}));
});
</script>
<div class="contentTableBox" style="width:100%">
	<div id="chart" style="height:600px;margin:auto"></div>
</div>
