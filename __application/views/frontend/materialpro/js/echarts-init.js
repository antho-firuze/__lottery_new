// ============================================================== 
// Line chart
// ============================================================== 
/* var dom = document.getElementById("line-chart");
var mytempChart = echarts.init(dom);
var app = {};
option = null;
option = {

    tooltip: { trigger: 'axis' },
    // legend: { data: ['max temp', 'min temp'] },
    toolbox: {
			show: true,
			feature: {
					magicType: { show: true, type: ['line', 'bar'] },
					restore: { show: true },
					saveAsImage: { show: true }
			}
    },
    color: ["#55ce63", "#009efb"],
    calculable: true,
    xAxis: [{
			type: 'category',
			boundaryGap: false,
			data: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    }],
    yAxis: [{
			type: 'value',
			axisLabel: { formatter: '{value} °C' }
    }],
    series: [{
			name: 'max temp',
			type: 'line',
			color: ['#000'],
			data: [11, 11, 15, 13, 12, 13, 10],
			itemStyle: {
					normal: {
							lineStyle: {
									shadowColor: 'rgba(0,0,0,0.3)',
									shadowBlur: 10,
									shadowOffsetX: 8,
									shadowOffsetY: 8
							}
					}
			},
				// markLine: {
						// data: [
								// { type: 'average', name: 'Average' }
						// ]
				// }
		}]
};

if (option && typeof option === "object") {
    mytempChart.setOption(option, true), $(function() {
        function resize() {
            setTimeout(function() {
                mytempChart.resize()
            }, 100)
        }
        $(window).on("resize", resize), 
				$(".sidebartoggler").on("click", resize),
				$(".view_detail").on("click", resize)
    });
} */

// ============================================================== 
// doughnut chart option
// ============================================================== 
var doughnutChart = echarts.init(document.getElementById('doughnut-chart'));

// specify chart configuration item and data

option = {
    tooltip: {
        trigger: 'item',
        formatter: "{a} <br/>{b} : {c} ({d}%)"
    },
    legend: {
        orient: 'vertical',
        x: 'left',
        data: ['Item A', 'Item B', 'Item C', 'Item D', 'Item E']
    },
    toolbox: {
        show: true,
        feature: {
            dataView: { show: true, readOnly: false },
            magicType: {
                show: true,
                type: ['pie', 'funnel'],
                option: {
                    funnel: {
                        x: '25%',
                        width: '50%',
                        funnelAlign: 'center',
                        max: 1548
                    }
                }
            },
            restore: { show: true },
            saveAsImage: { show: true }
        }
    },
    color: ["#f62d51", "#009efb", "#55ce63", "#ffbc34", "#2f3d4a"],
    calculable: true,
    series: [{
        name: 'Source',
        type: 'pie',
        radius: ['50%', '70%'],
        itemStyle: {
            normal: {
                label: {
                    show: false
                },
                labelLine: {
                    show: false
                }
            },
            emphasis: {
                label: {
                    show: true,
                    position: 'center',
                    textStyle: {
                        fontSize: '30',
                        fontWeight: 'bold'
                    }
                }
            }
        },
        data: [
            { value: 335, name: 'Item A' },
            { value: 310, name: 'Item B' },
            { value: 234, name: 'Item C' },
            { value: 135, name: 'Item D' },
            { value: 1548, name: 'Item E' }
        ]
    }]
};



// use configuration item and data specified to show chart
doughnutChart.setOption(option, true), $(function() {
    function resize() {
        setTimeout(function() {
            doughnutChart.resize()
        }, 100)
    }
    $(window).on("resize", resize), 
		$(".sidebartoggler").on("click", resize),
		$(".view_detail").on("click", resize)
});

