<script type="text/javascript">
var chart;
/*$(function(){
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
});*/
</script>
<form>
	<!--<div id="chart" style="height:600px;margin:auto"></div>-->
		<div class="item">
			<div class="title">公告<span class="right"><a href="#message/content/305">全部公告</a></span></div>
			<?=$notices?>
		</div>
		<div class="item">
			<div class="title">业绩汇总</div>
			<?=$staff_achievement?>
			<?=$summary_achievement?>
		</div>
</form>
