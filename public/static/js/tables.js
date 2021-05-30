var tableLabels={placeholder:lang.searching,perPage:lang.table_perpage,noRows:lang.nothing_found,info:lang.table_info,loading:lang.loading,infoFiltered:lang.table_infofiltered},financeTable=new JSTable("#finance_table",{sortable:!0,searchable:!0,perPage:parseInt(getCookie("perPage_financeTable",10)),truncatePager:!0,pagerDelta:2,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[6,7],sortable:!1,searchable:!1},{select:5,render:function(e,a){return e.innerHTML+" "+i18n.currency}}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.finances_table,ajaxParams:{from:jsObject.dateFrom,to:jsObject.dateTo}});financeTable.on("fetchData",(function(e){let a=document.querySelector("#finance_table tfoot tr th:nth-child(6)");a.innerHTML=Math.abs(e.sum)+" "+i18n.currency,e.sum>0?a.className="negative":a.className="positive"})),financeTable.on("perPageChange",(function(e,a){setCookie("perPage_financeTable",a)}));var categoryTable=new JSTable("#category_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[2,3],sortable:!1,searchable:!1}]}),categoryAssignmentTable=new JSTable("#category_assignment_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[4,5],sortable:!1,searchable:!1}]}),financesRecurringTable=new JSTable("#recurring_table",{perPage:10,labels:tableLabels,columns:[{select:3,sortable:!0,sort:"desc",render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:[4,5],render:function(e,a){let t=e.innerHTML;return t?moment(t).format(i18n.dateformatJS.date):""}},{select:[7,8],render:function(e,a){let t=e.innerHTML;return t?moment(t).format(i18n.dateformatJS.datetime):""}},{select:[10,11,12],sortable:!1,searchable:!1}]}),usersTable=new JSTable("#users_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[5,6,7,8,9],sortable:!1,searchable:!1}]});let mileageTables=document.querySelectorAll("table.mileage_year_table");mileageTables.forEach((function(e,a){new JSTable(e,{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"}]})}));var statsTable=new JSTable("#stats_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc"},{select:[1,2,3],render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:4,sortable:!1,searchable:!1}]}),statsYearTable=new JSTable("#stats_year_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment().month(t-1).format("MMMM")}},{select:[1,2,3],render:function(e,a){return e.innerHTML+" "+i18n.currency}}]}),statsMonthTable=new JSTable("#stats_month_table",{perPage:10,labels:tableLabels,columns:[{select:2,sortable:!0,sort:"desc"},{select:[2],render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:3,sortable:!1,searchable:!1}]}),statsCatTable=new JSTable("#stats_cat_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:3,sortable:!0,sort:"desc",render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:[4,5],sortable:!1,searchable:!1}]}),statsBudgetTable=new JSTable("#stats_budget_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:4,sortable:!0,sort:"desc",render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:[5,6],sortable:!1,searchable:!1}]}),carsTable=new JSTable("#cars_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),boardsTable=new JSTable("#boards_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),notificationsTable=new JSTable("#notifications_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[3,4],render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.datetime)}},{select:[5,6],sortable:!1,searchable:!1}]}),fuelTable=new JSTable("#fuel_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[9,10],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.fuel_table}),serviceTable=new JSTable("#service_table",{perPage:10,labels:tableLabels,searchable:!1,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[8,9],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount2,serverSide:!0,ajax:jsObject.service_table}),notificationsCategoryTable=new JSTable("#notifications_categories_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[1,2],sortable:!1,searchable:!1}]});const tokensTableContainer=document.getElementById("tokens_table");if(tokensTableContainer){let e="1"===tokensTableContainer.dataset.user?6:5;var tokensTable=new JSTable(tokensTableContainer,{perPage:10,labels:tableLabels,columns:[{select:1,sortable:!0,sort:"desc"},{select:[1,2],render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.datetime)}},{select:[e],sortable:!1,searchable:!1}]})}var crawlersTable=new JSTable("#crawlers_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2,3,4],sortable:!1,searchable:!1}]}),crawlersHeadersTable=new JSTable("#crawlers_headers_table",{perPage:10,labels:tableLabels,columns:[{select:4,sort:"asc",sortable:!0},{select:[5,6],sortable:!1,searchable:!1}]}),crawlersDataTable=new JSTable("#crawlers_data_table",{perPage:20,perPageSelect:[10,20,50,100,200],labels:tableLabels,sortable:!1,columns:[{select:[1],sort:"desc",sortable:!0,render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.datetime)}}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.crawler_table,ajaxParams:{from:jsObject.dateFrom,to:jsObject.dateTo}}),crawlersLinksTable=new JSTable("#crawlers_links_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[4,5],sortable:!1,searchable:!1}]}),crawlersDataSavedTable=new JSTable("#crawlers_data_saved_table",{perPage:20,labels:tableLabels,columns:[{select:[1],sort:"desc",sortable:!0,render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.datetime)}}],sortable:!1}),splitbillsGroupsTable=new JSTable("#splitbills_groups_table",{perPage:10,labels:tableLabels,columns:[{select:[1],render:function(e,a){let t=e.innerHTML;return""===t?"":t+" "+e.dataset.currency}},{select:[2,3,4],sortable:!1,searchable:!1}]});const splitbillsBillsTableContainer=document.getElementById("splitbills_bills_table");var splitbillsBillsTable=new JSTable(splitbillsBillsTableContainer,{perPage:10,labels:tableLabels,columns:[{select:0,sort:"desc",sortable:!0,render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[3,4,5],render:function(e,a){let t=e.innerHTML;return""===t?"":t+" "+splitbillsBillsTableContainer.dataset.currency}},{select:[6],render:function(e,a){let t=e.innerHTML;if(""===t)return"";let l="negative";return t>=0&&(l="positive"),"<span class='"+l+"'>"+t+" "+splitbillsBillsTableContainer.dataset.currency+"</span>"}},{select:[7,8],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.splitbill_table});const splitbillsBillsRecurringTableContainer=document.getElementById("splitbills_bills_recurring_table");var splitbillsBillsRecurringTable=new JSTable(splitbillsBillsRecurringTableContainer,{perPage:10,labels:tableLabels,columns:[{select:[1,2,3],render:function(e,a){let t=e.innerHTML;return""===t?"":t+" "+splitbillsBillsRecurringTableContainer.dataset.currency}},{select:[4],render:function(e,a){let t=e.innerHTML;if(""===t)return"";let l="negative";return t>=0&&(l="positive"),"<span class='"+l+"'>"+t+" "+splitbillsBillsRecurringTableContainer.dataset.currency+"</span>"}},{select:[5,6],render:function(e,a){let t=e.innerHTML;return t?moment(t).format(i18n.dateformatJS.date):""}},{select:[8,9],render:function(e,a){let t=e.innerHTML;return t?moment(t).format(i18n.dateformatJS.datetime):""}},{select:[11,12,13],sortable:!1,searchable:!1}]}),tripsTable=new JSTable("#trips_table",{perPage:10,labels:tableLabels,columns:[{select:[1],sort:"desc",sortable:!0,render:function(e,a){let t=e.innerHTML;return""===t?"":moment(t).format(i18n.dateformatJS.date)}},{select:[2],render:function(e,a){let t=e.innerHTML;return""===t?"":moment(t).format(i18n.dateformatJS.date)}},{select:[3,4],sortable:!1,searchable:!1}]}),stepsTable=new JSTable("#steps_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc"},{select:4,sortable:!1,searchable:!1}]}),stepsYearTable=new JSTable("#steps_year_table",{perPage:12,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment().month(t-1).format("MMMM")}},{select:4,sortable:!1,searchable:!1}]}),stepsMonthTable=new JSTable("#steps_month_table",{perPage:31,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[2],sortable:!1,searchable:!1}]}),mobileFavoritesTable=new JSTable("#mobile_favorites_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[2,3],sortable:!1,searchable:!1}]}),applicationPasswords=new JSTable("#application_passwords_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[1],sortable:!1,searchable:!1}]}),timesheetsProjectsTable=new JSTable("#timesheets_projects_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[1,2,3,4],sortable:!1,searchable:!1}]});const timesheetCategories=document.querySelector("#selected_categories");var timesheetsSheetsTable=new JSTable("#timesheets_sheets_table",{perPage:20,perPageSelect:[10,20,50,100,200],labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc"},{select:[4,5,6],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.timesheets_table,ajaxParams:{from:jsObject.dateFrom,to:jsObject.dateTo,categories:timesheetCategories?timesheetCategories.value:[]}});timesheetsSheetsTable.on("fetchData",(function(e){document.querySelector("#timesheets_sheets_table tfoot tr th:nth-child(4)").innerHTML=e.sum}));var timesheetsProjectCategoriesTable=new JSTable("#project_categories_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[1,2],sortable:!1,searchable:!1}]}),banlistTable=new JSTable("#banlist_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[3],sortable:!1,searchable:!1}]}),workoutMusclesTable=new JSTable("#workouts_muscles_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),workoutBodypartsTable=new JSTable("#workouts_bodyparts_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),workoutExercisesTable=new JSTable("#workouts_exercises_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),workoutPlansTable=new JSTable("#workouts_plans_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),workoutSessionsTable=new JSTable("#workouts_sessions_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){if(e.children.length>0){let a=e.children[0],t=a.innerHTML;t.match(/[0-9]{4}-[0-9]{2}-[0-9]{2}/)&&(a.innerHTML=moment(t).format(i18n.dateformatJS.date))}return e.innerHTML}},{select:[1,2,3],sortable:!1,searchable:!1}]}),workoutTemplateTable=new JSTable("#workouts_templates_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),recipesCookbooksTable=new JSTable("#recipes_cookbooks_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[1,2],sortable:!1,searchable:!1}]}),recipesIngredientsTable=new JSTable("#recipes_ingredients_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[1,2],sortable:!1,searchable:!1}]});