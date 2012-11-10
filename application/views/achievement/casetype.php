<? javascript('highcharts/highcharts') ?>
<script type="text/javascript">
var chart_casetype_income;
$(function(){
	chart_casetype_income = new Highcharts.Chart($.extend(true,{},highchartsOptions,{
        chart: {
            renderTo: 'chart_casetype_income'
        },
        title: {
            text: '今年截至上月底'
        },
        tooltip: {
    	    pointFormat: '{series.name}: <b>{point.percentage}%</b>',
        	percentageDecimals: 1
        },
        plotOptions: {
            pie: {
                allowPointSelect: true
            }
        },
        series: [{
            type: 'pie',
            data: <?=$chart_casetype_income_data?>
        }]
    }));
});
</script>
<div class="contentTableBox" style="width:100%">
	<div id="chart_casetype_income" style="height:600px;margin:auto"></div>
</div>