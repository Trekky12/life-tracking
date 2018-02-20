(function ($) {

    $(document).ready(function ( ) {

        /**
         * i18n
         */
        moment.locale(i18n.template);
        $.datepicker.setDefaults($.datepicker.regional[i18n.template]);


        $('#go-back-btn').on('click', function (e) {
            window.history.back();
        });

        $('#cancel').on('click', function (e) {
            e.preventDefault();
            //location.href = '';
            window.history.back();
        });

        /**
         * Delete
         */
        $('body').on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var url = $(this).data('url');
            deleteObject(url);
        });

        function deleteObject(url) {
            if (!confirm(lang.really_delete)) {
                return false;
            }
            $.ajax({
                url: url,
                method: 'DELETE',
                success: function (response) {
                    console.log(response['is_deleted'] === true);
                    window.location.reload();
                },
                error: function (data) {
                    alert(data);
                }
            });
        }

        /**
         * Datepicker on Location
         */
        var from = $("#fromSelect").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            altField: "#from",
            altFormat: "yy-mm-dd"
        }).on("change", function () {
            to.datepicker("option", "minDate", getDate(this));
        });
        if ($("#from").val()) {
            $('#fromSelect').datepicker("setDate", moment($("#from").val()).toDate());
        }

        var to = $("#toSelect").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            altFormat: "yy-mm-dd",
            altField: "#to"
        }).on("change", function () {
            from.datepicker("option", "maxDate", getDate(this));
        });
        if ($("#to").val()) {
            $('#toSelect').datepicker("setDate", moment($("#to").val()).toDate());
        }

        function getDate(element) {
            var date;
            try {
                var dateFormat = $(element).datepicker("option", "dateFormat");
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }
            return date;
        }

        /**
         * Datepicker on Add Fuel or Add Finances
         */

        $("#dateSelect").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            altFormat: "yy-mm-dd",
            altField: '#inputDate'
        });
        if ($("#inputDate").val()) {
            $('#dateSelect').datepicker("setDate", moment($("#inputDate").val()).toDate());
        }


        /**
         * Datepicker on monthly finance entries
         */
        $("#dateStart").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            altFormat: "yy-mm-dd",
            altField: '#inputStart'
        });
        if ($("#inputStart").val()) {
            $('#dateStart').datepicker("setDate", moment($("#inputStart").val()).toDate());
        }

        $("#dateEnd").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            altFormat: "yy-mm-dd",
            altField: '#inputEnd'
        });
        if ($("#inputEnd").val()) {
            $('#dateEnd').datepicker("setDate", moment($("#inputEnd").val()).toDate());
        }


        /**
         * Fix Chrome for Android Timepicker
         * @see https://stephen.rees-carter.net/thought/html5-time-input-and-chrome-for-android-stupidity
         */
        /*$('input[type=time]').change(function () {
         $(this).val($(this).val().replace(/(:\d\d:)(\d\d)$/, '\$100'));
         });*/

        /**
         * Alert
         */
        $('span.closebtn').on('click', function () {
            $(this).parent().hide();
        });


        /**
         * Charts
         */
        

        if ($("#financeSummaryChart").length) {
            var fuelChart = new Chart($("#financeSummaryChart"), {
                data: {
                    labels: $("#financeSummaryChart").data('labels'),
                    datasets: [
                        {
                            label: $("#financeSummaryChart").data('label1'),
                            data: $("#financeSummaryChart").data('values1'),
                            backgroundColor: '#FF0000'
                        },
                        {
                            label: $("#financeSummaryChart").data('label2'),
                            data: $("#financeSummaryChart").data('values2'),
                            backgroundColor: '#008800'
                        }
                    ]
                },
                type: 'bar',
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                                ticks: {
                                    min: 0
                                }
                            }]
                    }
                }
            });
        }

        var defaultColors = ['#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#3B3EAC', '#0099C6', '#DD4477', '#66AA00', '#B82E2E', '#316395', '#994499', '#22AA99', '#AAAA11', '#6633CC', '#E67300', '#8B0707', '#329262', '#5574A6', '#3B3EAC'];

        if ($("#financeDetailChart").length) {
            var fuelChart = new Chart($("#financeDetailChart"), {
                data: {
                    labels: $("#financeDetailChart").data('labels'),
                    datasets: [
                        {

                            backgroundColor: defaultColors,
                            data: $("#financeDetailChart").data('values')
                        }
                    ]
                },
                type: 'pie',
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'top'
                    }
                }
            });
        }


        /**
         * Tables
         */

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
            "ajax": jsObject.finances_table,
            "deferLoading": jsObject.datacount
        });

        $("#category_table").DataTable({
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

        $("#monthly_table").DataTable({
            "paging": false,
            "info": false,
            "order": [
                [3, 'desc']
            ],
            "columnDefs": [
                {
                    "targets": [6, 7],
                    "orderable": false
                },
                {
                    "targets": [1, 2],
                    "render": function (data, type, row) {
                        return data ? moment(data).format(i18n.dateformatJS) : "";
                    }
                },
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

        $("#stats_table").DataTable({
            "language": {
                "url": jsObject.datatable
            },
            "paging": true,
            "order": [[0, 'desc'], [1, 'desc']],
            "columnDefs": [
                {
                    "targets": [0],
                    "render": function (data, type, row) {

                        /*if (type === 'display' || type === 'filter') {
                         return moment().month(data - 1).format("MMMM");
                         }*/

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
                    "targets": [3],
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


        /**
         * Common finances
         */
        if ($('#checkboxCommon').length) {

            $('#checkboxCommon').on('change', function () {
                $('#commonValue').toggle();

                var value = $('#inputValue').val();
                
                if (value) {
                    if ($(this).is(':checked')) {
                        // move value to common Value and the half into value
                        $('#inputCommonValue').val(value);
                        $('#inputValue').val(value / 2);
                    } else {
                        // move commonValue to value and reset commonValue
                        $('#inputValue').val($('#inputCommonValue').val());
                        $('#inputCommonValue').val(null);
                    }
                }
            });

        }


    });
})(jQuery);
