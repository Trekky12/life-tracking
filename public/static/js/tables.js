var tableLabels={placeholder:lang.searching,perPage:lang.table_perpage,noRows:lang.nothing_found,info:lang.table_info,loading:lang.loading,infoFiltered:lang.table_infofiltered},financeTable=new JSTable("#finance_table",{sortable:!0,searchable:!0,perPage:10,truncatePager:!0,pagerDelta:2,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[6,7],sortable:!1,searchable:!1},{select:5,render:function(e,a){return e.innerHTML+" "+i18n.currency}}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.finances_table,ajaxParams:{from:jsObject.dateFrom,to:jsObject.dateTo}});financeTable.on("fetchData",function(e){this.table.getFooterRow().setCellContent(5,null),this.table.getFooterRow().setCellContent(5,Math.abs(e.sum)+" "+i18n.currency),e.sum>0?this.table.getFooterRow().setCellClass(5,"negative"):this.table.getFooterRow().setCellClass(5,"positive")});var categoryTable=new JSTable("#category_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[2,3],sortable:!1,searchable:!1}]}),categoryAssignmentTable=new JSTable("#category_assignment_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[4,5],sortable:!1,searchable:!1}]}),financesRecurringTable=new JSTable("#recurring_table",{perPage:10,labels:tableLabels,columns:[{select:3,sortable:!0,sort:"desc",render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:[8,9],sortable:!1,searchable:!1},{select:[4,5],render:function(e,a){let t=e.innerHTML;return t?moment(t).format(i18n.dateformatJS.date):""}},{select:7,render:function(e,a){let t=e.innerHTML;return t?moment(t).format(i18n.dateformatJS.datetime):""}}]}),usersTable=new JSTable("#users_table",{perPage:10,labels:tableLabels,columns:[{select:[5,6,7],sortable:!1,searchable:!1}]});let mileageTables=document.querySelectorAll("table.mileage_year_table");mileageTables.forEach(function(e,a){new JSTable(e,{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"}]})});var statsTable=new JSTable("#stats_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc"},{select:[1,2,3],render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:4,sortable:!1,searchable:!1}]}),statsYearTable=new JSTable("#stats_year_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment().month(t-1).format("MMMM")}},{select:[1,2,3],render:function(e,a){return e.innerHTML+" "+i18n.currency}}]}),statsMonthTable=new JSTable("#stats_month_table",{perPage:10,labels:tableLabels,columns:[{select:2,sortable:!0,sort:"desc"},{select:[2],render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:3,sortable:!1,searchable:!1}]}),statsCatTable=new JSTable("#stats_cat_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:3,sortable:!0,sort:"desc",render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:[4,5],sortable:!1,searchable:!1}]}),statsBudgetTable=new JSTable("#stats_budget_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:4,sortable:!0,sort:"desc",render:function(e,a){return e.innerHTML+" "+i18n.currency}},{select:[5,6],sortable:!1,searchable:!1}]}),carsTable=new JSTable("#cars_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),boardsTable=new JSTable("#boards_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2],sortable:!1,searchable:!1}]}),notificationsTable=new JSTable("#notifications_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[3,4],render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.datetime)}},{select:[5,6],sortable:!1,searchable:!1}]}),fuelTable=new JSTable("#fuel_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[9,10],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.fuel_table}),serviceTable=new JSTable("#service_table",{perPage:10,labels:tableLabels,searchable:!1,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[8,9],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount2,serverSide:!0,ajax:jsObject.service_table}),notificationsCategoryTable=new JSTable("#notifications_categories_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[1,2],sortable:!1,searchable:!1}]}),tokensCategoryTable=new JSTable("#tokens_table",{perPage:10,labels:tableLabels,columns:[{select:[3,4],render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.datetime)}},{select:4,sortable:!0,sort:"desc"},{select:[5],sortable:!1,searchable:!1}]}),crawlersTable=new JSTable("#crawlers_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[1,2,3,4],sortable:!1,searchable:!1}]}),crawlersHeadersTable=new JSTable("#crawlers_headers_table",{perPage:10,labels:tableLabels,columns:[{select:4,sort:"asc",sortable:!0},{select:[5,6],sortable:!1,searchable:!1}]}),crawlersDataTable=new JSTable("#crawlers_data_table",{perPage:20,perPageSelect:[10,20,50,100,200],labels:tableLabels,sortable:!1,columns:[{select:[0],sort:"desc",sortable:!0,render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.datetime)}}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.crawler_table,ajaxParams:{from:jsObject.dateFrom,to:jsObject.dateTo}}),crawlersLinksTable=new JSTable("#crawlers_links_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc",sortable:!0},{select:[4,5],sortable:!1,searchable:!1}]}),splitbillsGroupsTable=new JSTable("#splitbills_groups_table",{perPage:10,labels:tableLabels,columns:[{select:[1],render:function(e,a){let t=e.innerHTML;return""===t?"":t+" "+e.dataset.currency}},{select:[2,3],sortable:!1,searchable:!1}]});const splitbillsBillsTableContainer=document.getElementById("splitbills_bills_table");var splitbillsBillsTable=new JSTable(splitbillsBillsTableContainer,{perPage:10,labels:tableLabels,columns:[{select:0,sort:"desc",sortable:!0,render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}},{select:[3,4,5],render:function(e,a){let t=e.innerHTML;return""===t?"":t+" "+splitbillsBillsTableContainer.dataset.currency}},{select:[6],render:function(e,a){let t=e.innerHTML;if(""===t)return"";let l="negative";return t>=0&&(l="positive"),"<span class='"+l+"'>"+t+" "+splitbillsBillsTableContainer.dataset.currency+"</span>"}},{select:[7,8],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.splitbill_table}),tripsTable=new JSTable("#trips_table",{perPage:10,labels:tableLabels,columns:[{select:[1],sort:"desc",sortable:!0,render:function(e,a){let t=e.innerHTML;return""===t?"":moment(t).format(i18n.dateformatJS.date)}},{select:[2],render:function(e,a){let t=e.innerHTML;return""===t?"":moment(t).format(i18n.dateformatJS.date)}},{select:[3,4],sortable:!1,searchable:!1}]}),stepsTable=new JSTable("#steps_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc"},{select:4,sortable:!1,searchable:!1}]}),stepsYearTable=new JSTable("#steps_year_table",{perPage:12,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment().month(t-1).format("MMMM")}},{select:4,sortable:!1,searchable:!1}]}),stepsMonthTable=new JSTable("#steps_month_table",{perPage:31,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e,a){let t=e.innerHTML;return moment(t).format(i18n.dateformatJS.date)}}]});