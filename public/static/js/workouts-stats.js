"use strict";const workoutCharts=document.querySelectorAll(".workoutChart");workoutCharts.forEach((function(t){let e=[];JSON.parse(t.dataset.values).forEach((function(t,a){e.push({label:lang.workouts_set+" "+(a+1),data:t,spanGaps:!0,fill:!1,borderColor:randomColor({hue:"blue",luminosity:"bright"})})})),new Chart(t,{type:"line",data:{datasets:e},options:{maintainAspectRatio:!1,scales:{xAxes:[{type:"time",time:{unit:"day"}}],yAxes:[{ticks:{stepSize:1}}]}}})}));