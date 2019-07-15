var tableLabels = {
    placeholder: lang.searching,
    perPage: lang.table_perpage,
    noRows: lang.nothing_found,
    info: lang.table_info,
    loading: lang.loading,
    infoFiltered: lang.table_infofiltered
};

var financeTable = new JSTable("#finance_table", {
    sortable: true,
    searchable: true,
    perPage: 10,
    truncatePager: true,
    pagerDelta: 2,
    //firstLast : true,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: [6, 7],
            sortable: false,
            searchable: false
        },
        {
            select: 5,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        }
    ],
    deferLoading: jsObject.datacount,
    serverSide: true,
    ajax: jsObject.finances_table,
    ajaxParams: {
        "from": jsObject.dateFrom,
        "to": jsObject.dateTo
    }
});

/*financeTable.on("sort", function (column, direction) {
 console.log("is sorted");
 });*/

financeTable.on("fetchData", function (data) {
    this.table.getFooterRow().setCellContent(5, null);
    //if (data.recordsFiltered < data.recordsTotal) {
    this.table.getFooterRow().setCellContent(5, Math.abs(data.sum) + " " + i18n.currency);
    if (data.sum > 0) {
        this.table.getFooterRow().setCellClass(5, "negative");
    }else{
        this.table.getFooterRow().setCellClass(5, "positive");
    }
    //}
});


var categoryTable = new JSTable("#category_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [2, 3],
            sortable: false,
            searchable: false
        }
    ]
});


var categoryAssignmentTable = new JSTable("#category_assignment_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [4, 5],
            sortable: false,
            searchable: false
        }
    ]
});

var financesRecurringTable = new JSTable("#recurring_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 3,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        },
        {
            select: [8, 9],
            sortable: false,
            searchable: false
        },
        {
            select: [4, 5],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data ? moment(data).format(i18n.dateformatJS.date) : "";
            }
        },
        {
            select: 7,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data ? moment(data).format(i18n.dateformatJS.datetime) : "";
            }
        }
    ]
});

var usersTable = new JSTable("#users_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: [5, 6, 7],
            sortable: false,
            searchable: false
        }
    ]
});

let mileageTables = document.querySelectorAll('table.mileage_year_table');
mileageTables.forEach(function (item, idx) {
    new JSTable(item, {
        perPage: 10,
        labels: tableLabels,
        columns: [
            {
                select: 0,
                sortable: true,
                sort: "asc"
            }
        ]
    });
});

var statsTable = new JSTable("#stats_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc"
        },
        {
            select: [1, 2, 3],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        },
        {
            select: 4,
            sortable: false,
            searchable: false
        }
    ]
});

var statsYearTable = new JSTable("#stats_year_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment().month(data - 1).format("MMMM");
            }
        },
        {
            select: [1, 2, 3],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        }
    ]
});

var statsMonthTable = new JSTable("#stats_month_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 2,
            sortable: true,
            sort: "desc"
        },
        {
            select: [2],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        },
        {
            select: 3,
            sortable: false,
            searchable: false
        }
    ]
});

var statsCatTable = new JSTable("#stats_cat_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: 3,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        },
        {
            select: [4, 5],
            sortable: false,
            searchable: false
        }
    ]
});

var statsBudgetTable = new JSTable("#stats_budget_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: 4,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        },
        {
            select: [5, 6],
            sortable: false,
            searchable: false
        }
    ]
});

var carsTable = new JSTable("#cars_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sort: "asc",
            sortable: true
        },
        {
            select: [1, 2],
            sortable: false,
            searchable: false
        }
    ]
});

var boardsTable = new JSTable("#boards_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sort: "asc",
            sortable: true
        },
        {
            select: [1, 2],
            sortable: false,
            searchable: false
        }
    ]
});

var notificationsTable = new JSTable("#notifications_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sort: "asc",
            sortable: true
        },
        {
            select: [3, 4],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.datetime);
            }
        },
        {
            select: [5, 6],
            sortable: false,
            searchable: false
        }
    ]
});

var fuelTable = new JSTable("#fuel_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: [9, 10],
            sortable: false,
            searchable: false
        }
    ],
    deferLoading: jsObject.datacount,
    serverSide: true,
    ajax: jsObject.fuel_table
});


var serviceTable = new JSTable("#service_table", {
    perPage: 10,
    labels: tableLabels,
    searchable: false,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: [8, 9],
            sortable: false,
            searchable: false
        }
    ],
    deferLoading: jsObject.datacount2,
    serverSide: true,
    ajax: jsObject.service_table
});


var notificationsCategoryTable = new JSTable("#notifications_categories_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [1, 2],
            sortable: false,
            searchable: false
        }
    ]
});

var tokensCategoryTable = new JSTable("#tokens_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: [3, 4],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.datetime);
            }
        },
        {
            select: 4,
            sortable: true,
            sort: "desc"
        },
        {
            select: [5],
            sortable: false,
            searchable: false
        }
    ]
});

var crawlersTable = new JSTable("#crawlers_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sort: "asc",
            sortable: true
        },
        {
            select: [1, 2, 3, 4],
            sortable: false,
            searchable: false
        }
    ]
});

var crawlersHeadersTable = new JSTable("#crawlers_headers_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 4,
            sort: "asc",
            sortable: true
        },
        {
            select: [5, 6],
            sortable: false,
            searchable: false
        }
    ]
});

var crawlersDataTable = new JSTable("#crawlers_data_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    sortable: false,
    columns: [
        {
            select: [0],
            sort: "desc",
            sortable: true,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.datetime);
            }
        }
    ],
    deferLoading: jsObject.datacount,
    serverSide: true,
    ajax: jsObject.crawler_table,
    ajaxParams: {
        "from": jsObject.dateFrom,
        "to": jsObject.dateTo
    }
});

var crawlersLinksTable = new JSTable("#crawlers_links_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sort: "asc",
            sortable: true
        },
        {
            select: [4, 5],
            sortable: false,
            searchable: false
        }
    ]
});

var splitbillsGroupsTable = new JSTable("#splitbills_groups_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: [1],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if(data === ""){
                    return "";
                }
                return data + " " + cell.dataset.currency;
            }
        },
        {
            select: [2, 3],
            sortable: false,
            searchable: false
        }
    ]
});

const splitbillsBillsTableContainer = document.getElementById('splitbills_bills_table');
var splitbillsBillsTable = new JSTable(splitbillsBillsTableContainer, {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: 0,
            sort: "desc",
            sortable: true,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: [3, 4, 5],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if(data === ""){
                    return "";
                }
                return data + " " + splitbillsBillsTableContainer.dataset.currency;
            }
        },
        {
            select: [6],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if(data === ""){
                    return "";
                }
                
                let dataClass = "negative";
                if (data >= 0) {
                    dataClass = "positive";
                }
                return "<span class='" + dataClass + "'>" + data + " " + splitbillsBillsTableContainer.dataset.currency + "</span>";
            }
        },
        {
            select: [7, 8],
            sortable: false,
            searchable: false
        }
    ],
    deferLoading: jsObject.datacount,
    serverSide: true,
    ajax: jsObject.splitbill_table
});

var tripsTable = new JSTable("#trips_table", {
    perPage: 10,
    labels: tableLabels,
    columns: [
        {
            select: [1],
            sort: "desc",
            sortable: true,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if(data === ""){
                    return "";
                }
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: [2],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if(data === ""){
                    return "";
                }
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: [3, 4],
            sortable: false,
            searchable: false
        }
    ]
});