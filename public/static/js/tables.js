var tableLabels={placeholder:"Suche...",perPage:"{select} Elemente pro Seite",noRows:"Nichts gefunden",info:"Zeige {start} bis {end} von {rows} Elementen",loading:"Lade...",infoFiltered:"Zeige {start} bis {end} von {rows} Elemente (gefiltert von {rowsTotal} Elementen)"},financeTable=new JSTable("#finance_table",{sortable:!0,searchable:!0,perPage:10,truncatePager:!0,pagerDelta:2,labels:tableLabels,columns:[{select:0,type:"date",format:"MYSQL",sortable:!0,sort:"desc",render:function(e){return moment(e).format(i18n.dateformatJS)}},{select:[6,7],sortable:!1,searchable:!1},{select:5,render:function(e){return e+" "+i18n.currency}}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.finances_table});financeTable.on("fetchData",function(e){this.table.getFooterRow().setCellContent(5,null),e.recordsFiltered<e.recordsTotal&&this.table.getFooterRow().setCellContent(5,e.sum+" "+i18n.currency)});var categoryTable=new JSTable("#category_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[2,3],sortable:!1,searchable:!1}]}),categoryAssignmentTable=new JSTable("#category_assignment_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[4,5],sortable:!1,searchable:!1}]}),financesRecurringTable=new JSTable("#recurring_table",{perPage:10,labels:tableLabels,columns:[{select:3,sortable:!0,sort:"desc",render:function(e){return e+" "+i18n.currency}},{select:[8,9],sortable:!1,searchable:!1},{select:[4,5],type:"date",format:"MYSQL",render:function(e){return e?moment(e).format(i18n.dateformatJS):""}},{select:7,type:"date",format:"MYSQL",render:function(e){return e?moment(e).format(i18n.dateformatJSFull):""}}]}),usersTable=new JSTable("#users_table",{perPage:10,labels:tableLabels,columns:[{select:[5,6,7],sortable:!1,searchable:!1}]}),mileageYearTable=new JSTable(".mileage_year_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"}]}),statsTable=new JSTable("#stats_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc"},{select:[1,2,3],render:function(e){return e+" "+i18n.currency}},{select:4,sortable:!1,searchable:!1}]}),statsYearTable=new JSTable("#stats_year_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"desc",render:function(e){return moment().month(e-1).format("MMMM")}},{select:[1,2,3],render:function(e){return e+" "+i18n.currency}}]}),statsMonthTable=new JSTable("#stats_month_table",{perPage:10,labels:tableLabels,columns:[{select:2,sortable:!0,sort:"desc"},{select:[2],render:function(e){return e+" "+i18n.currency}},{select:3,sortable:!1,searchable:!1}]}),statsCatTable=new JSTable("#stats_cat_table",{perPage:10,labels:tableLabels,columns:[{select:0,type:"date",format:"MYSQL",sortable:!0,sort:"desc",render:function(e){return moment(e).format(i18n.dateformatJS)}},{select:3,sortable:!0,sort:"desc",render:function(e){return e+" "+i18n.currency}},{select:[4,5],sortable:!1,searchable:!1}]}),statsBudgetTable=new JSTable("#stats_budget_table",{perPage:10,labels:tableLabels,columns:[{select:0,type:"date",format:"MYSQL",sortable:!0,sort:"desc",render:function(e){return moment(e).format(i18n.dateformatJS)}},{select:4,sortable:!0,sort:"desc",render:function(e){return e+" "+i18n.currency}},{select:[5,6],sortable:!1,searchable:!1}]}),carsTable=new JSTable("#cars_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc"},{select:[1,2],sortable:!1,searchable:!1}]}),boardsTable=new JSTable("#boards_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc"},{select:[1,2],sortable:!1,searchable:!1}]}),notificationsTable=new JSTable("#notifications_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc"},{select:[3,4],render:function(e){return moment(e).format(i18n.dateformatJSFull)}},{select:[5,6],sortable:!1,searchable:!1}]}),fuelTable=new JSTable("#fuel_table",{perPage:10,labels:tableLabels,columns:[{select:0,type:"date",format:"MYSQL",sortable:!0,sort:"desc",render:function(e){return moment(e).format(i18n.dateformatJS)}},{select:[9,10],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.fuel_table}),serviceTable=new JSTable("#service_table",{perPage:10,labels:tableLabels,searchable:!1,columns:[{select:0,type:"date",format:"MYSQL",sortable:!0,sort:"desc",render:function(e){return moment(e).format(i18n.dateformatJS)}},{select:[8,9],sortable:!1,searchable:!1}],deferLoading:jsObject.datacount2,serverSide:!0,ajax:jsObject.service_table}),notificationsCategoryTable=new JSTable("#notifications_categories_table",{perPage:10,labels:tableLabels,columns:[{select:0,sortable:!0,sort:"asc"},{select:[1,2],sortable:!1,searchable:!1}]}),tokensCategoryTable=new JSTable("#tokens_table",{perPage:10,labels:tableLabels,columns:[{select:[3,4],render:function(e){return moment(e).format(i18n.dateformatJSFull)}},{select:4,sortable:!0,sort:"desc"},{select:[5],sortable:!1,searchable:!1}]}),crawlersTable=new JSTable("#crawlers_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc"},{select:[1,2,3,4],sortable:!1,searchable:!1}]}),crawlersHeadersTable=new JSTable("#crawlers_headers_table",{perPage:10,labels:tableLabels,columns:[{select:4,sort:"asc"},{select:[5,6],sortable:!1,searchable:!1}]}),crawlersDataTable=new JSTable("#crawlers_data_table",{perPage:20,perPageSelect:[10,20,50,100,200],labels:tableLabels,sortable:!1,columns:[{select:[0],sort:"desc",sortable:!0,render:function(e){return moment(e).format(i18n.dateformatJSFull)}}],deferLoading:jsObject.datacount,serverSide:!0,ajax:jsObject.crawler_table,ajaxParams:{from:jsObject.crawler_filter_from,to:jsObject.crawler_filter_to}}),crawlersLinksTable=new JSTable("#crawlers_links_table",{perPage:10,labels:tableLabels,columns:[{select:0,sort:"asc"},{select:[4,5],sortable:!1,searchable:!1}]});