"use strict";let stepsSummaryChart=document.querySelector("#stepsSummaryChart");stepsSummaryChart&&new Chart(stepsSummaryChart,{data:{labels:JSON.parse(stepsSummaryChart.dataset.labels),datasets:[{label:stepsSummaryChart.dataset.label,data:JSON.parse(stepsSummaryChart.dataset.values),backgroundColor:"#1e88e5"}]},type:"bar",options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{ticks:{min:0}}}}});