<script type="text/javascript">
var chart_monthly_queries,chart_personally_queries,chart_personally_type_queries;
$(function(){
	chart_monthly_queries = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_monthly_queries'
		},
		title: {
			text: '每月新增咨询和案件数量'
		},
		xAxis: {
			categories: $.parseJSON('<?=$chart_monthly_queries_catogary?>')
		},
		series: $.parseJSON('<?=$chart_monthly_queries_series?>')
	}));

	chart_personally_queries = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_personally_queries',type: 'column'
		},
		title: {
			text: '每人咨询量（今年，按在谈状态）'
		},
		xAxis: {
			categories: $.parseJSON('<?=$chart_personally_queries_catogary?>')
		},
		yAxis: {
			stackLabels: {
				enabled: true
			}
		},
		plotOptions: {
			column: {
				stacking: 'normal',
				dataLabels: {
					enabled: true,
					color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
				}
			}
		},
		series: $.parseJSON('<?=$chart_personally_queries_series?>')
	}));

	chart_personally_type_queries = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
		chart: {
			renderTo: 'chart_personally_type_queries',type: 'column'
		},
		title: {
			text: '每人咨询量（今年，按咨询方式）'
		},
		xAxis: {
			categories: $.parseJSON('<?=$chart_personally_type_queries_catogary?>')
		},
		yAxis: {
			stackLabels: {
				enabled: true
			}
		},
		plotOptions: {
			column: {
				stacking: 'normal',
				dataLabels: {
					enabled: true,
					color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
				}
			}
		},
		series: $.parseJSON('<?=$chart_personally_type_queries_series?>')
	}));
});
</script>
<div class="contentTableBox" style="width:100%">
	<div id="chart_monthly_queries" style="height:600px;margin:auto"></div>
	<div id="chart_personally_queries" style="height:600px;margin:auto"></div>
	<div id="chart_personally_type_queries" style="height:600px;margin:auto"></div>
</div>
