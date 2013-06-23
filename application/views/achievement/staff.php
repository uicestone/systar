<script type="text/javascript">
var chart_<?=METHOD?>,chart_<?=METHOD?>_count;
$(function(){
	
	var categories=$.parseJSON('<?=$category?>');
	var series=$.parseJSON('<?=$series?>');
	
	chart_<?=METHOD?> = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_<?=METHOD?>',type: 'bar',
			height:categories.length * 3*30
		},
		title: {
			text: '<?=$this->output->title?>'+'金额'
		},
		plotOptions: {
			bar: {
				stacking: 'normal'
			}
		},
		xAxis: {
			categories: categories
		},
		yAxis: {
			opposite: true
		},
		series: series
	}));
	
	var categories=$.parseJSON('<?=$category_count?>');
	var series=$.parseJSON('<?=$series_count?>');
	
	chart_<?=METHOD?>_count = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_<?=METHOD?>_count',type: 'bar',
			height:categories.length * 3*30
		},
		title: {
			text: '<?=$this->output->title?>'+'数量'
		},
		plotOptions: {
			bar: {
				stacking: 'normal'
			}
		},
		xAxis: {
			categories: categories
		},
		yAxis: {
			opposite: true
		},
		series: series
	}));
});
</script>
<div class="contentTableBox">
	<div id="chart_<?=METHOD?>" style="width:98%"></div>
	<div id="chart_<?=METHOD?>_count" style="width:98%"></div>
</div>
