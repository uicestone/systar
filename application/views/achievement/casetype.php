<? javascript('highcharts/highcharts') ?>
<script type="text/javascript">
var chart_casetype_income;
$(function(){

	chart_casetype_income = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
        chart: {
            renderTo: 'chart_casetype_income'
        },
        title: {
            text: '今年截至上月底案件分类创收'
        },
        tooltip: {
    	    pointFormat: '{series.name}: <b>￥{point.y}</b>',
        	percentageDecimals: 1
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: true,
					color: '#000000',
					style:{
							fontFamily:'Microsoft Yahei',
							fontSize:'16px'
						},
					formatter: function() {
						return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage*10)/10 +' %';
					}
				},
				showInLegend: true
            }
        },
        series: [{
            type: 'pie',
			name: '今年创收',
            data: <?=$chart_casetype_income_data?>
        }]
    }));
});
</script>
<div class="contentTableBox" style="width:100%">
	<div id="chart_casetype_income" style="height:600px;margin:auto"></div>
</div>