"use strict";const exercisesSelected=document.querySelector("#workoutExerciseSelection .content");document.addEventListener("click",(function(e){let t=e.target.closest(".exercise .plus"),s=e.target.closest(".minus"),a=e.target.closest(".exercise .headline"),l=e.target.closest(".exercise"),d=e.target.closest(".add_set"),n=e.target.closest(".remove_set"),c=e.target.closest("#add_workout_day"),r=e.target.closest("#add_superset"),i=exercisesSelected.querySelectorAll('[data-type="workout-element"]').length;if(s){e.preventDefault();let t=s.parentElement.parentElement;if("day"===t.dataset.category){let e=[];for(e.push(t),t=t.nextElementSibling;t&&"day"!==t.dataset.category;)e.push(t),t=t.nextElementSibling;e.forEach((function(e){e.remove()}))}else t.remove();loadSelectedMuscles()}if(t){e.preventDefault();let t=l.cloneNode(!0);t.classList.add("selected"),t.querySelector(".plus").classList.add("hidden"),t.querySelector(".minus").classList.remove("hidden"),t.querySelector(".handle").classList.remove("hidden"),t.querySelector(".sets").classList.remove("hidden");let s=document.createElement("input");s.type="hidden",s.name="exercises["+i+"][id]",s.value=l.dataset.id,t.appendChild(s),t.querySelectorAll("input").forEach((function(e,t){e.name.includes("dummy")||(e.setAttribute("name",e.name.replace(/exercises\[[^\]]*\]/,"exercises["+i+"]")),e.removeAttribute("disabled"))})),exercisesSelected.appendChild(t),loadSelectedMuscles()}if(a&&(e.preventDefault(),e.target.parentElement.classList.toggle("active")),d){console.log("add set");let e=l.querySelector(".sets .set-list"),t=l.querySelector(".sets .set-dummy"),s=l.querySelectorAll(".sets .set").length,a=t.cloneNode(!0);a.classList.remove("hidden"),a.classList.remove("set-dummy"),a.querySelector(".set-nr").innerHTML=s,a.querySelectorAll("input").forEach((function(e,t){e.setAttribute("name",e.name.replace("dummy",s-1)),e.removeAttribute("disabled")})),e.appendChild(a)}if(n){let e=l.querySelectorAll(".sets .set:not(.set-dummy)");if(e.length>0){e[e.length-1].remove()}}if(c){e.preventDefault();let t=document.createElement("div");t.classList.add("workout_day_split"),t.dataset.type="workout-element",t.dataset.category="day";let s=document.createElement("div");s.classList.add("content");let a=document.createElement("input");a.type="hidden",a.name="exercises["+i+"][type]",a.value="day";let l=document.createElement("input");l.type="text",l.name="exercises["+i+"][notice]",l.required=!0;let d=document.createElement("div");d.classList.add("icons");let n=document.createElement("i");n.classList.add("minus"),n.classList.add("fas"),n.classList.add("fa-minus");let c=document.createElement("i");c.classList.add("handle"),c.classList.add("fas"),c.classList.add("fa-arrows-alt"),d.appendChild(n),d.appendChild(c),s.appendChild(a),s.appendChild(l),t.appendChild(s),t.appendChild(d),exercisesSelected.appendChild(t)}if(r){e.preventDefault();let t=document.createElement("div");t.classList.add("workout_superset"),t.dataset.type="workout-element";let s=document.createElement("div");s.classList.add("content");let a=document.createElement("h2");a.innerHTML=lang.workouts_superset;let l=document.createElement("input");l.type="hidden",l.name="exercises["+i+"][type]",l.value="superset";let d=document.createElement("div");d.classList.add("exercises"),d.dataset.type="superset";let n=document.createElement("div");n.classList.add("icons");let c=document.createElement("i");c.classList.add("minus"),c.classList.add("fas"),c.classList.add("fa-minus");let r=document.createElement("i");r.classList.add("handle"),r.classList.add("fas"),r.classList.add("fa-arrows-alt"),n.appendChild(c),n.appendChild(r),s.appendChild(a),s.appendChild(l),s.appendChild(d),t.appendChild(s),t.appendChild(n),exercisesSelected.appendChild(t),createSortable(d)}}));const workoutSupersets=document.querySelectorAll(".workout_superset .exercises");function createSortable(e){new Sortable(e,{group:{name:"exercise"},swapThreshold:.5,fallbackOnBody:!0,handle:".handle",dataIdAttr:"data-id",onUpdate:function(e){updateFields()},onAdd:function(e){let t=e.to.dataset.type,s=e.item.querySelector('input[name*="is_child"]');s.value="main"===t?0:1,updateFields()}})}function updateFields(){exercisesSelected.querySelectorAll('[data-type="workout-element"]').forEach((function(e,t){e.querySelectorAll("input").forEach((function(e){e.setAttribute("name",e.name.replace(/exercises\[[^\]]*\]/,"exercises["+t+"]"))}))}))}workoutSupersets.forEach((function(e,t){createSortable(e)})),createSortable(exercisesSelected);const usedMusclesWrapper=document.querySelector("#usedMusclesWrapper");function loadSelectedMuscles(){if(usedMusclesWrapper){let e=[];exercisesSelected.querySelectorAll(".exercise").forEach((function(t,s){e.push(t.dataset.id)}));let t={exercises:e};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.workouts_exercises_selected_muscles,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){"error"!==e.status&&(usedMusclesWrapper.innerHTML=e.data)})).catch((function(e){console.log(e)}))}}