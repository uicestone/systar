<script type="text/javascript">
var chart;
$(function(){
	chart = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart',
			type:'line'
		},
		title: {
			text: '全所总创收和签约'
		},
		xAxis: {
			categories: <?=$category?>
		},
		plotOptions: {
			line:{
				lineWidth:3
			}
		},
		series: <?=$series?>
	}));
});
</script>
<div class="contentTableBox" style="width:100%">
	<?=$table?>
	<div id="chart" style="height:600px;margin:auto"></div>
</div>
