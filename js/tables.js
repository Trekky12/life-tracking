(function ($) {

    $(document).ready(function () {

        var totalFinancesSum = null;
        $("#finance_table").DataTable({
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "info": true,
            "responsive": true,
            "autoWidth": false,
            "order": [[0, 'desc'], [1, 'desc']],
            "columnDefs": [
                {
                    "targets": [0],
                    "render": function (data, type, row) {
                        return moment(data).format(i18n.dateformatJS);
                    },
                    "responsivePriority": -1
                },
                // edit
                {
                    "targets": [6, 7],
                    "orderable": false,
                    "responsivePriority": 3
                },
                // time, category
                {
                    "targets": [1, 3],
                    "responsivePriority": 2
                },
                // type
                {
                    "targets": [2],
                    "responsivePriority": 1
                },
                // description
                {
                    "targets": [4],
                    "responsivePriority": -1
                },
                //value
                {
                    "targets": [5],
                    "render": function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data + " " + i18n.currency;
                        }
                        return data;
                    },
                    "responsivePriority": -1
                }
            ],
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": jsObject.finances_table,
                "dataSrc": function (data) {
                    totalFinancesSum = data.recordsTotal !== data.recordsFiltered ? data.sum : null;
                    return data.data;
                }
            },
            "deferLoading": jsObject.datacount,
            //@see https://datatables.net/forums/discussion/comment/130741/#Comment_130741
            "drawCallback": function (settings) {
                var api = this.api();
                var content = "";
                if (totalFinancesSum !== null) {
                    content = totalFinancesSum + " " + i18n.currency;
                }

                if (!api.column(5).responsiveHidden()) {
                    $(api.column(4).footer()).html(content);
                } else {
                    $(api.column(5).footer()).html(content);
                }
            }
        });
        $("#category_table").DataTable({
            "paging": true,
            "info": true,
            "columnDefs": [
                {
                    "targets": [2, 3],
                    "orderable": false
                }
            ],
            "language": {
                "url": jsObject.datatable
            },
            "responsive": true,
            "autoWidth": false
        });
        $("#category_assignment_table").DataTable({
            "paging": true,
            "info": true,
            "columnDefs": [
                {
                    "targets": [4, 5],
                    "orderable": false
                }
            ],
            "language": {
                "url": jsObject.datatable
            },
            "responsive": true,
            "autoWidth": false
        });
        $("#recurring_table").DataTable({
            "paging": true,
            "info": true,
            "order": [
                [3, 'desc']
            ],
            "columnDefs": [
                {
                    "targets": [8, 9],
                    "orderable": false
                },
                {
                    "targets": [4, 5],
                    "render": function (data, type, row) {
                        return data ? moment(data).format(i18n.dateformatJS) : "";
                    }
                },
                {
                    "targets": [7],
                    "render": function (data, type, row) {
                        return data ? moment(data).format(i18n.dateformatJSFull) : "";
                    }
                },
                {
                    "targets": [3],
                    "render": function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data + " " + i18n.currency;
                        }
                        return data;
                    },
                    "responsivePriority": -1
                }
            ],
            "language": {
                "url": jsObject.datatable
            },
            "responsive": true,
            "autoWidth": false
        });
        $("#users_table").DataTable({
            "paging": false,
            "info": false,
            "columnDefs": [
                {
                    "targets": [5, 6, 7],
                    "orderable": false
                }
            ],
            "language": {
                "url": jsObject.datatable
            },
            "responsive": true,
            "autoWidth": false
        });
        $("#fuel_table").DataTable({
            "order": [[0, 'desc']],
            "columnDefs": [
                {
                    "targets": [9, 10],
                    "orderable": false
                },
                {
                    "targets": [0],
                    "render": function (data, type, row) {
                        return moment(data).format(i18n.dateformatJS);
                    }
                }
            ],
            "dom": '<"top"f>rt<"bottom"ip>',
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "info": true,
            "responsive": true,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "ajax": jsObject.fuel_table,
            "deferLoading": jsObject.datacount
        });
        $("#service_table").DataTable({
            "order": [[0, 'desc']],
            "columnDefs": [
                {
                    "targets": [8, 9],
                    "orderable": false
                },
                {
                    "targets": [0],
                    "render": function (data, type, row) {
                        return moment(data).format(i18n.dateformatJS);
                    }
                }
            ],
            "dom": '<"top"f>rt<"bottom"ip>',
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "info": true,
            "responsive": true,
            "autoWidth": false,
            "processing": true,
            "serverSide": true,
            "ajax": jsObject.service_table,
            "deferLoading": jsObject.datacount2
        });
        $(".mileage_year_table").DataTable({
            "order": [[0, 'asc']],
            "dom": '<"top">rt<"bottom"ip>',
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "info": true,
            "responsive": true,
            "autoWidth": false
        });
        $("#stats_table").DataTable({
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "order": [[0, 'desc']],
            "columnDefs": [
                {
                    "targets": [0],
                    "render": function (data, type, row) {
                        return data;
                    },
                    "responsivePriority": -1
                },
                {
                    "targets": [1, 2, 3],
                    "render": function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data + " " + i18n.currency;
                        }
                        return data;
                    }
                },
                {
                    "targets": [4],
                    "orderable": false
                }
            ],
            "responsive": true,
            "autoWidth": false
        });
        $("#stats_year_table").DataTable({
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "order": [[0, 'desc']],
            "columnDefs": [
                {
                    "targets": [0],
                    "render": function (data, type, row) {

                        if (type === 'display' || type === 'filter') {
                            return moment().month(data - 1).format("MMMM");
                        }

                        return data;
                    },
                    "responsivePriority": -1
                },
                {
                    "targets": [1, 2, 3],
                    "render": function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data + " " + i18n.currency;
                        }
                        return data;
                    },
                    "responsivePriority": -1
                }
            ],
            "responsive": true,
            "autoWidth": false
        });
        $("#stats_month_table").DataTable({
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "order": [[2, 'desc'], [0, 'asc']],
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                {
                    "targets": [3],
                    "orderable": false
                },
                {
                    "targets": [0],
                    "responsivePriority": -1
                },
                {
                    "targets": [2],
                    "render": function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data + " " + i18n.currency;
                        }
                        return data;
                    },
                    "responsivePriority": -1
                }
            ]
        });
        $("#stats_cat_table").DataTable({
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "order": [[1, 'desc'], [0, 'asc']],
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                {
                    "targets": [2, 3],
                    "orderable": false
                },
                {
                    "targets": [0],
                    "responsivePriority": -1
                },
                {
                    "targets": [1],
                    "render": function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data + " " + i18n.currency;
                        }
                        return data;
                    },
                    "responsivePriority": -1
                }
            ]
        });
        $("#cars_table").DataTable({
            "paging": true,
            "info": true,
            "columnDefs": [
                {
                    "targets": [1, 2],
                    "orderable": false
                }
            ],
            "language": {
                "url": jsObject.datatable
            },
            "responsive": true,
            "autoWidth": false
        });
        $("#boards_table").DataTable({
            "paging": true,
            "info": true,
            "columnDefs": [
                {
                    "targets": [1, 2],
                    "orderable": false
                }
            ],
            "language": {
                "url": jsObject.datatable
            },
            "responsive": true,
            "autoWidth": false
        });
        $("#stats_budget_table").DataTable({
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "order": [[2, 'desc'], [1, 'asc'], [0, 'asc']],
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                {
                    "targets": [3, 4],
                    "orderable": false
                },
                {
                    "targets": [0],
                    "responsivePriority": -1
                },
                {
                    "targets": [2],
                    "render": function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data + " " + i18n.currency;
                        }
                        return data;
                    },
                    "responsivePriority": -1
                }
            ]
        });
        $("#notifications_table").DataTable({
            "paging": true,
            "info": true,
            "columnDefs": [
                {
                    "targets": [5, 6],
                    "orderable": false
                }
            ],
            "language": {
                "url": jsObject.datatable
            },
            "responsive": true,
            "autoWidth": false
        });


    });
}
)(jQuery);