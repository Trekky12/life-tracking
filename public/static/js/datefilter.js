"use strict";var datepickerRange=document.getElementById("dateRange"),datepickerStart=document.getElementById("inputStart"),datepickerEnd=document.getElementById("inputEnd");datepickerRange&&datepickerStart&&datepickerEnd&&flatpickr(datepickerRange,{altInput:!0,altFormat:i18n.twig,dateFormat:"Y-m-d",locale:i18n.template,mode:"range",defaultDate:[datepickerStart.value,datepickerEnd.value],onChange:function(e){const t=e.map(e=>this.formatDate(e,"Y-m-d"));datepickerStart.value="",datepickerEnd.value="",t.length>0&&(datepickerStart.value=t[0],datepickerEnd.value=t[0]),t.length>1&&(datepickerEnd.value=t[1])}});