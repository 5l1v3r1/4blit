window.chartColors = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
};

function drawLineChart() {
    var jsonData = $.ajax({
	url: '/ajaxCb.php?action=getGraphData',
	method: 'GET',
	dataType: 'json',
	success: function(result) {
	    renderGraph(result.labels,result.clicks, result.posts);
	}
    });
}

function renderGraph(labels, clicks, posts) {
    ctx = document.getElementById("myChart").getContext("2d");

    var lineChartData = {
	labels: labels,
        datasets: [{
            label: "Clicks",
	    borderColor: window.chartColors.red,
	    backgroundColor: window.chartColors.red,
	    fill: false,
            data: clicks
        }, {
            label: "Posts",
	    borderColor: window.chartColors.blue,
	    backgroundColor: window.chartColors.blue,
	    fill: false,
            data: posts
	}]
    };

    var lineChartOptions = {
    };

    var myChart = new Chart(ctx, {
	type: 'line',
	data: lineChartData,
	options: lineChartOptions
    });
}

$(document).ready(function(){
    drawLineChart();
});