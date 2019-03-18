var tableLabels = {
    placeholder: "Suche...",
    perPage: "{select} Elemente pro Seite",
    noRows: "Nichts gefunden",
    info: "Zeige {start} bis {end} von {rows} Elementen",
    loading: "Lade...",
    infoFiltered: "Zeige {start} bis {end} von {rows} Elemente (gefiltert von {rowsTotal} Elementen)"
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
            type: "date",
            format: "MYSQL",
            sortable: true,
            sort: "desc",
            render: function (data) {
                return moment(data).format(i18n.dateformatJS);
            }
        },
        {
            select: [6, 7],
            sortable: false,
            searchable: false
        },
        {
            select: 5,
            render: function (data) {
                return data + " " + i18n.currency;
            }
        }
    ],
    deferLoading: jsObject.datacount,
    serverSide: true,
    ajax: jsObject.finances_table
});

/*financeTable.on("sort", function (column, direction) {
 console.log("is sorted");
 });*/

financeTable.on("fetchData", function (data) {
    this.table.getFooterRow().setCellContent(5, null);
    if (data.recordsFiltered < data.recordsTotal) {
        this.table.getFooterRow().setCellContent(5, data.sum + " " + i18n.currency);
    }
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
            render: function (data) {
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
            type: "date",
            format: "MYSQL",
            render: function (data) {
                return data ? moment(data).format(i18n.dateformatJS) : "";
            }
        },
        {
            select: 7,
            type: "date",
            format: "MYSQL",
            render: function (data) {
                return data ? moment(data).format(i18n.dateformatJSFull) : "";
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

var mileageYearTable = new JSTable(".mileage_year_table", {
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
            render: function (data) {
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
            render: function (data) {
                return moment().month(data - 1).format("MMMM");
            }
        },
        {
            select: [1, 2, 3],
            render: function (data) {
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
            render: function (data) {
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
            type: "date",
            format: "MYSQL",
            sortable: true,
            sort: "desc",
            render: function (data) {
                return moment(data).format(i18n.dateformatJS);
            }
        },
        {
            select: 3,
            sortable: true,
            sort: "desc",
            render: function (data) {
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
            type: "date",
            format: "MYSQL",
            sortable: true,
            sort: "desc",
            render: function (data) {
                return moment(data).format(i18n.dateformatJS);
            }
        },
        {
            select: 4,
            sortable: true,
            sort: "desc",
            render: function (data) {
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
            sort: "asc"
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
            sort: "asc"
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
            sort: "asc"
        },
        {
            select: [3,4],
            render: function (data) {
                return moment(data).format(i18n.dateformatJSFull);
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
            type: "date",
            format: "MYSQL",
            sortable: true,
            sort: "desc",
            render: function (data) {
                return moment(data).format(i18n.dateformatJS);
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
            type: "date",
            format: "MYSQL",
            sortable: true,
            sort: "desc",
            render: function (data) {
                return moment(data).format(i18n.dateformatJS);
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
            select: [3,4],
            render: function (data) {
                return moment(data).format(i18n.dateformatJSFull);
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
            sort: "asc"
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
            sort: "asc"
        },
        {
            select: [5,6],
            sortable: false,
            searchable: false
        }
    ]
});

var crawlersDataTable = new JSTable("#crawlers_data_table", {
    perPage: 20,
    perPageSelect: [10, 20,50,100,200],
    labels: tableLabels,
    sortable:false,
    columns: [
        {
            select: [0],
            sort: "desc",
            sortable: true,
            render: function (data) {
                return moment(data).format(i18n.dateformatJSFull);
            }
        }
    ],
    deferLoading: jsObject.datacount,
    serverSide: true,
    ajax: jsObject.crawler_table,
    ajaxParams: {
        "from" : jsObject.crawler_filter_from,
        "to" : jsObject.crawler_filter_to
    }
});