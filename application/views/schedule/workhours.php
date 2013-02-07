<div class="contentTableBox">
	<?=$work_hour_stat?>
	<div id="chart_staffly_workhours" style="height:600px;margin:auto"></div>
</div>
<script type="text/javascript">
var chart_staffly_workhours;
$(function(){
	chart_staffly_workhours = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_staffly_workhours',
			type:'column'
		},
		title: {
			text: '上周工作时间'
		},
		xAxis: {
			categories: $.parseJSON('<?=$chart_staffly_workhours_catogary?>')
		},
		series: $.parseJSON('<?=$chart_staffly_workhours_series?>')
	}));
});
</script>