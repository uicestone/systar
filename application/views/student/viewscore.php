<script type="text/javascript" src="js/highcharts/highcharts.js"></script>
<script type="text/javascript">
var chart;
$(function(){
	chart = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart'
		},
		title: {
			text: '年级排名'
		},
		subtitle: {
			text: '最近几次考试各学科的排名走势',
			y:40
		},
		xAxis: {
			categories: $.parseJSON('<? echo $category?>')
		},
		yAxis: {
			reversed:true,
			min:1
		},
		tooltip: {
			formatter: function() {
					return '<b>'+ this.series.name +'</b><br/>'+
					this.x +': 第'+ this.y+'名';
			}
		},
		series: $.parseJSON('<? echo $series ?>')
	}));
});
</script>
<div class="contentTableBox">
	<div id="chart" style="width:900px;height:500px;margin:auto"></div>
	<? $this->arrayExportTable($scores,NULL,false,false,array(),false,true) ?>
</div>
