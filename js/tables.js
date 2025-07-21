var tableLabels = {
    placeholder: lang.searching,
    perPage: lang.table_perpage,
    noRows: lang.nothing_found,
    info: lang.table_info,
    loading: lang.loading,
    infoFiltered: lang.table_infofiltered
};

var layout = {
    top: "{search}{select}",
    bottom: "{pager}{info}"
};

var financeTable = new JSTable("#finance_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    sortable: true,
    searchable: true,
    perPage: parseInt(getCookie("perPage_financeTable", 10)),
    truncatePager: true,
    pagerDelta: 2,
    //firstLast : true,
    labels: tableLabels,
    layout: layout,
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
    let footer = document.querySelector("#finance_table tfoot tr th:nth-child(6)");
    footer.innerHTML = Math.abs(data.sum) + " " + i18n.currency;
    if (data.sum > 0) {
        footer.className = "negative";
    } else {
        footer.className = "positive";
    }
});

financeTable.on("perPageChange", function (old_value, new_value) {
    setCookie("perPage_financeTable", new_value);
});


var categoryTable = new JSTable("#category_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
            select: [4, 5],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data ? moment(data).format(i18n.dateformatJS.date) : "";
            }
        },
        {
            select: [7, 8],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data ? moment(data).format(i18n.dateformatJS.datetime) : "";
            }
        },
        {
            select: [10, 11, 12],
            sortable: false,
            searchable: false
        }
    ]
});

var financesAccountTable = new JSTable("#finances_account_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: 1,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        },
        {
            select: [2, 3],
            sortable: false,
            searchable: false
        }
    ]
});

var financesMethodTable = new JSTable("#finances_method_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [3, 4],
            sortable: false,
            searchable: false
        }
    ]
});

var financeTransactionTable = new JSTable('#finance_transaction_table', {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 1,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: 4,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
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
    ajax: jsObject.finances_transaction_table
});

var usersTable = new JSTable("#users_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [5, 6, 7, 8, 9],
            sortable: false,
            searchable: false
        }
    ]
});

let mileageTables = document.querySelectorAll('table.mileage_year_table');
mileageTables.forEach(function (item, idx) {
    new JSTable(item, {
        perPage: 20,
        perPageSelect: [10, 20, 50, 100, 200],
        labels: tableLabels,
        layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

const tokensTableContainer = document.getElementById('tokens_table');
if (tokensTableContainer) {
    let lastColumn = tokensTableContainer.dataset.user === "1" ? 6 : 5;
    var tokensTable = new JSTable(tokensTableContainer, {
        perPage: 20,
        perPageSelect: [10, 20, 50, 100, 200],
        labels: tableLabels,
        layout: layout,
        columns: [
            {
                select: 1,
                sortable: true,
                sort: "desc"
            },
            {
                select: [1, 2],
                render: function (cell, idx) {
                    let data = cell.innerHTML;
                    return moment(data).format(i18n.dateformatJS.datetime);
                }
            },
            {
                select: [lastColumn],
                sortable: false,
                searchable: false
            }
        ]
    });
}

var crawlersTable = new JSTable("#crawlers_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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
    layout: layout,
    sortable: false,
    columns: [
        {
            select: [1],
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
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var crawlersDataSavedTable = new JSTable("#crawlers_data_saved_table", {
    perPage: 20,
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: [1],
            sort: "desc",
            sortable: true,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return moment(data).format(i18n.dateformatJS.datetime);
            }
        }
    ],
    sortable: false
});

var splitbillsGroupsTable = new JSTable("#splitbills_groups_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: [1],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if (data === "") {
                    return "";
                }
                return data + " " + cell.dataset.currency;
            }
        },
        {
            select: [2, 3, 4],
            sortable: false,
            searchable: false
        }
    ]
});

const splitbillsBillsTableContainer = document.getElementById('splitbills_bills_table');
if (splitbillsBillsTableContainer) {
    let columns = [
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
                if (data === "") {
                    return "";
                }
                return data + " " + splitbillsBillsTableContainer.dataset.currency;
            }
        },
        {
            select: [6],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if (data === "") {
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
    ];

    if (splitbillsBillsTableContainer.dataset.members < 2) {
        columns = [
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
                select: [3],
                render: function (cell, idx) {
                    let data = cell.innerHTML;
                    if (data === "") {
                        return "";
                    }
                    return data + " " + splitbillsBillsTableContainer.dataset.currency;
                }
            },
            {
                select: [4, 5],
                sortable: false,
                searchable: false
            }
        ];
    }

    var splitbillsBillsTable = new JSTable(splitbillsBillsTableContainer, {
        perPage: 20,
        perPageSelect: [10, 20, 50, 100, 200],
        labels: tableLabels,
        layout: layout,
        columns: columns,
        deferLoading: jsObject.datacount,
        serverSide: true,
        ajax: jsObject.splitbill_table
    });
}

const splitbillsBillsRecurringTableContainer = document.getElementById('splitbills_bills_recurring_table');
if (splitbillsBillsRecurringTableContainer) {

    let columns = [
        {
            select: [1, 2, 3],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if (data === "") {
                    return "";
                }
                return data + " " + splitbillsBillsRecurringTableContainer.dataset.currency;
            }
        },
        {
            select: [4],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if (data === "") {
                    return "";
                }

                let dataClass = "negative";
                if (data >= 0) {
                    dataClass = "positive";
                }
                return "<span class='" + dataClass + "'>" + data + " " + splitbillsBillsRecurringTableContainer.dataset.currency + "</span>";
            }
        },
        {
            select: [5, 6],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data ? moment(data).format(i18n.dateformatJS.date) : "";
            }
        },
        {
            select: [8, 9],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data ? moment(data).format(i18n.dateformatJS.datetime) : "";
            }
        },
        {
            select: [11, 12, 13],
            sortable: false,
            searchable: false
        }
    ];

    if (splitbillsBillsRecurringTableContainer.dataset.members < 2) {
        columns = [
            {
                select: [1],
                render: function (cell, idx) {
                    let data = cell.innerHTML;
                    if (data === "") {
                        return "";
                    }
                    return data + " " + splitbillsBillsRecurringTableContainer.dataset.currency;
                }
            },
            {
                select: [2, 3],
                render: function (cell, idx) {
                    let data = cell.innerHTML;
                    return data ? moment(data).format(i18n.dateformatJS.date) : "";
                }
            },
            {
                select: [5, 6],
                render: function (cell, idx) {
                    let data = cell.innerHTML;
                    return data ? moment(data).format(i18n.dateformatJS.datetime) : "";
                }
            },
            {
                select: [8, 9, 10],
                sortable: false,
                searchable: false
            }
        ];

    }

    var splitbillsBillsRecurringTable = new JSTable(splitbillsBillsRecurringTableContainer, {
        perPage: 20,
        perPageSelect: [10, 20, 50, 100, 200],
        labels: tableLabels,
        layout: layout,
        columns: columns
    });
}

var tripsTable = new JSTable("#trips_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: [1],
            sort: "desc",
            sortable: true,
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if (data === "") {
                    return "";
                }
                return moment(data).format(i18n.dateformatJS.date);
            }
        },
        {
            select: [2],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                if (data === "") {
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


var stepsTable = new JSTable("#steps_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc"
        },
        {
            select: 4,
            sortable: false,
            searchable: false
        }
    ]
});

var stepsYearTable = new JSTable("#steps_year_table", {
    perPage: 12,
    labels: tableLabels,
    layout: layout,
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
            select: 4,
            sortable: false,
            searchable: false
        }
    ]
});

var stepsMonthTable = new JSTable("#steps_month_table", {
    perPage: 31,
    labels: tableLabels,
    layout: layout,
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
            select: [2],
            sortable: false,
            searchable: false
        }
    ]
});

var mobileFavoritesTable = new JSTable("#mobile_favorites_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var applicationPasswords = new JSTable("#application_passwords_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [1],
            sortable: false,
            searchable: false
        }
    ]
});

var timesheetsProjectsTable = new JSTable("#timesheets_projects_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [1, 2, 3, 4, 5],
            sortable: false,
            searchable: false
        }
    ]
});

const timesheetCategories = document.querySelector("#selected_categories");
const timesheetInvoiced = document.querySelector("#timesheet_view_invoiced");
const timesheetBilled = document.querySelector("#timesheet_view_billed");
const timesheetPayed = document.querySelector("#timesheet_view_payed");
const timesheetHappened = document.querySelector("#timesheet_view_happened");
const timesheetCustomer = document.querySelector("#timesheet_view_customer");

const timesheetsSheetsTableContainer = document.getElementById('timesheets_sheets_table');
if (timesheetsSheetsTableContainer) {
    const hasEnd = timesheetsSheetsTableContainer.dataset.hasEnd === "1";

    var timesheetsSheetsTable = new JSTable(timesheetsSheetsTableContainer, {
        perPage: 100,
        perPageSelect: [20, 50, 100, 200, 500],
        labels: tableLabels,
        layout: layout,
        columns: [
            {
                select: 1,
                sortable: true,
                sort: "desc"
            },
            {
                select: hasEnd ? [0, 6, 7, 8, 9] : [0, 4, 5, 6, 7],
                sortable: false,
                searchable: false
            }
        ],
        deferLoading: jsObject.datacount,
        serverSide: true,
        ajax: jsObject.timesheets_table,
        ajaxParams: {
            "from": jsObject.dateFrom,
            "to": jsObject.dateTo,
            "categories": timesheetCategories ? timesheetCategories.value : [],
            "invoiced": timesheetInvoiced ? timesheetInvoiced.value : '',
            "billed": timesheetBilled ? timesheetBilled.value : '',
            "payed": timesheetPayed ? timesheetPayed.value : '',
            "happened": timesheetHappened ? timesheetHappened.value : '',
            "customer": timesheetCustomer ? timesheetCustomer.value : '',
        }
    });

    if (hasEnd) {
        timesheetsSheetsTable.on("fetchData", function (data) {
            let footer = document.querySelector("#timesheets_sheets_table tfoot tr th:nth-child(5)");
            footer.innerHTML = data.sum;
        });
    }
    timesheetsSheetsTable.on("update", function () {
        let selected_items = document.querySelector("#tableFooterFilter #selected_items");
        selected_items.innerHTML = 0;
    });
}

var timesheetsProjectCategoriesTable = new JSTable("#project_categories_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var timesheetsCategoryBudgetTable = new JSTable("#project_categorybudgets_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: 1,
            sortable: true
        },
        {
            select: [2, 3, 4],
            sortable: false,
            searchable: false
        }
    ]
});

var timesheetsCustomersTable = new JSTable("#project_customers_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [2, 3, 4],
            sortable: false,
            searchable: false
        }
    ]
});

var banlistTable = new JSTable("#banlist_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [3],
            sortable: false,
            searchable: false
        }
    ]
});

var workoutMusclesTable = new JSTable("#workouts_muscles_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var workoutBodypartsTable = new JSTable("#workouts_bodyparts_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var workoutExercisesTable = new JSTable("#workouts_exercises_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var workoutPlansTable = new JSTable("#workouts_plans_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sort: "asc",
            sortable: true
        },
        {
            select: [3, 4],
            sortable: false,
            searchable: false
        }
    ]
});

var workoutSessionsTable = new JSTable("#workouts_sessions_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                if (cell.children.length > 0) {
                    let link = cell.children[0];
                    let data = link.innerHTML;

                    if (data.match(/[0-9]{4}-[0-9]{2}-[0-9]{2}/)) {
                        link.innerHTML = moment(data).format(i18n.dateformatJS.date);
                    }
                }
                return cell.innerHTML;
            }
        },
        {
            select: [1, 2, 3],
            sortable: false,
            searchable: false
        }
    ]
});

var workoutTemplateTable = new JSTable("#workouts_templates_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sort: "asc",
            sortable: true
        },
        {
            select: [3, 4],
            sortable: false,
            searchable: false
        }
    ]
});

var recipesCookbooksTable = new JSTable("#recipes_cookbooks_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var recipesGroceriesTable = new JSTable("#recipes_groceries_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [3, 4],
            sortable: false,
            searchable: false
        }
    ]
});

var recipesMealplansTable = new JSTable("#recipes_mealplans_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var recipesShopppingListsTable = new JSTable("#recipes_shoppinglists_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var noticefieldsTable = new JSTable("#noticefields_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
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

var requirementsTypesTable = new JSTable("#requirement_types_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [2, 3, 4],
            sortable: false,
            searchable: false
        }
    ]
});


var requirementsTypesTable = new JSTable("#customers_requirements_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 0,
            sortable: true,
            sort: "asc"
        },
        {
            select: [3],
            sortable: false,
            searchable: false
        }
    ]
});


var transactionsRecurringTable = new JSTable("#transaction_recurring_table", {
    perPage: 20,
    perPageSelect: [10, 20, 50, 100, 200],
    labels: tableLabels,
    layout: layout,
    columns: [
        {
            select: 1,
            sortable: true,
            sort: "desc",
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data + " " + i18n.currency;
            }
        },
        {
            select: [4, 5],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data ? moment(data).format(i18n.dateformatJS.date) : "";
            }
        },
        {
            select: [7, 8],
            render: function (cell, idx) {
                let data = cell.innerHTML;
                return data ? moment(data).format(i18n.dateformatJS.datetime) : "";
            }
        },
        {
            select: [10, 11, 12],
            sortable: false,
            searchable: false
        }
    ]
});

