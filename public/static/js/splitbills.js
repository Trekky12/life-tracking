"use strict";const splitbillsForm=document.getElementById("splitbillsBillsForm"),splittbillsButtons=document.querySelectorAll(".splitbill_btn"),calcButton=document.getElementById("calculateExchangeRate");if(splitbillsForm){let e=splitbillsForm.querySelectorAll("input.balance_paid"),t=splitbillsForm.querySelectorAll("input.balance_spend"),l=splitbillsForm.querySelector("#inputValue"),n=splitbillsForm.querySelector("#remaining_paid"),i=splitbillsForm.querySelector("#remaining_spend"),a=splitbillsForm.querySelectorAll("input.balance_paid_foreign"),o=splitbillsForm.querySelectorAll("input.balance_spend_foreign"),u=splitbillsForm.querySelector("#inputValueForeign"),r=splitbillsForm.querySelector("#inputRate"),s=splitbillsForm.querySelector("#inputFee");function c(e){let t=0;return e.forEach((function(e){let l=Number.parseFloat(e.value);l&&(t+=l)})),t.toFixed(2)}function p(){return""==l.value?0:parseFloat(l.value).toFixed(2)}function d(e,t){let l=c(e),n=p();t.innerHTML=(n-l).toFixed(2)}function f(){let e=u.value/r.value;return e+=e*(parseFloat(s.value)/100),e.toFixed(2)}function v(){return parseFloat(u.value)/parseFloat(l.value)}function b(){l.value=f(),a.forEach((function(e,t){e.value=0})),o.forEach((function(e,t){e.value=0})),e.forEach((function(e,t){e.value=0})),t.forEach((function(e,t){e.value=0})),d(e,n),d(t,i)}l.addEventListener("input",(function(l){e.forEach((function(e){e.value=0})),t.forEach((function(e){e.value=0})),d(e,n),d(t,i),splittbillsButtons.forEach((function(e){e.classList.add("button-outlined")}))})),splittbillsButtons.forEach((function(r,s){r.addEventListener("click",(function(s){s.preventDefault();let c=r.dataset.category,p=r.dataset.type,f=r.dataset.id,b=parseFloat(l.value),m=u?parseFloat(u.value):b;if(b>0){let l=r.parentNode.nextElementSibling;if(l.classList.add("hidden"),splittbillsButtons.forEach((function(e){e.dataset.category==c&&e.classList.add("button-outlined")})),r.classList.remove("button-outlined"),"paid"==c&&(splitbillsForm.querySelector('input[name="paid_by"]').value="person"==p?f:p),"spend"==c&&(splitbillsForm.querySelector('input[name="spend_by"]').value="person"==p?f:p),"same"==p)"paid"==c?(equal_splitting(e,b),e.forEach((function(e,t){a[t]&&(a[t].value=(e.value*v()).toFixed(2))}))):"spend"==c&&(equal_splitting(t,b),t.forEach((function(e,t){o[t]&&(o[t].value=(e.value*v()).toFixed(2))})));else if("person"==p){if("paid"==c){e.forEach((function(e){e.value=0})),splitbillsForm.querySelector('input[name="balance['+f+'][paid]"]').value=b,a.forEach((function(e){e.value=0}));let t=splitbillsForm.querySelector('input[name="balance['+f+'][paid_foreign]"]');t&&(t.value=m)}else if("spend"==c){t.forEach((function(e){e.value=0})),splitbillsForm.querySelector('input[name="balance['+f+'][spend]"]').value=b,o.forEach((function(e){e.value=0}));let e=splitbillsForm.querySelector('input[name="balance['+f+'][spend_foreign]"]');e&&(e.value=m)}}else"individual"==p&&l.classList.remove("hidden");d(e,n),d(t,i)}}))})),splitbillsForm.addEventListener("submit",(function(l){let n=c(e),i=c(t),a=p();a===n&&a===i||(l.preventDefault(),console.log(a),console.log(n),console.log(i),document.getElementById("loading-overlay").classList.add("hidden"),alert(lang.splitbills_numbers_wrong))})),e.forEach((function(t,l){t.addEventListener("input",(function(t){d(e,n)}))})),t.forEach((function(e,l){e.addEventListener("input",(function(e){d(t,i)}))})),r&&r.addEventListener("input",(function(e){b()})),s&&s.addEventListener("input",(function(e){b()})),u&&(u.addEventListener("input",(function(a){l.value=f(),d(e,n),d(t,i)})),a.forEach((function(l,a){l.addEventListener("input",(function(o){e[a].value=(l.value/v()).toFixed(2),d(e,n),d(t,i)}))})),o.forEach((function(l,a){l.addEventListener("input",(function(o){t[a].value=(l.value/v()).toFixed(2),d(e,n),d(t,i)}))})))}function equal_splitting(e,t){let l=[].slice.call(e).sort((function(){return.5-Math.random()})),n=l.length;l.forEach((function(e){let l=(t/n).toFixed(2);e.value=l,n--,t-=l}))}