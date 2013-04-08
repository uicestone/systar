<script type="text/javascript">
var chart_<?=METHOD?>,chart_<?=METHOD?>_count;
$(function(){
	
	var categories=$.parseJSON('<?=$category?>');
	var series=$.parseJSON('<?=$series?>');
	
	chart_<?=METHOD?> = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_<?=METHOD?>',type: 'column'
		},
		title: {
			text: '<?=$this->section_title?>'+'金额'
		},
		plotOptions: {
			column: {
				stacking: 'normal'
			}
		},
		xAxis: {
			categories: categories
		},
		series: series
	}));
	
	var categories=$.parseJSON('<?=$category_count?>');
	var series=$.parseJSON('<?=$series_count?>');
	
	chart_<?=METHOD?>_count = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_<?=METHOD?>_count',type: 'column'
		},
		title: {
			text: '<?=$this->section_title?>'+'数量'
		},
		plotOptions: {
			bar: {
				stacking: 'normal'
			}
		},
		xAxis: {
			categories: categories
		},
		series: series
	}));
});
</script>
<div class="contentTableBox">
	<div id="chart_<?=METHOD?>" style="width:99%"></div>
	<div id="chart_<?=METHOD?>_count" style="width:99%"</div>
</div>
