(function ($) {

    $(document).ready(function ( ) {
        
        add_chosen();
        
        $('select.category').each(function () {
            var $select = $(this);
            get_monthly_costs($select);
        });
        


        var template = $('#budgetTemplate').html();
        Mustache.parse(template);
        
        $('#add_budget').on('click', function (e) {
            e.preventDefault();
            // Get already selected categories
            
            var index = $('.budget-entry').length;

            var rendered = Mustache.render(template, {index: index});

            $('#budgetForm .budget-entry.remaining').before(rendered);
            
            add_chosen();

            index++;
        });


        $('body').on('change', 'select.category', function (e) {
            var $select = $(this);
            
            get_monthly_costs($select);
            //category_costs
            var $description = $select.parent().parent().find('input.description');

            // Set default description based on category
            if ($description.val().length === 0) {
                $description.val($select.find('option:selected').text());
            }
        });

        $('body').on('click', '.btn-delete', function (e) {
            if (!$(this).data('url')) {
                e.preventDefault();
                $(this).parent().remove();
            }
        });

        $('body').on('change keyup', 'input.value', function (e) {
            var income = parseFloat($('#remaining_budget').data('income'));
            var sum = 0;
            $('input.value').each(function () {
                sum += parseFloat($(this).val());
            });
            $('#remaining_budget').text(income - sum);
        });

        function add_chosen() {
            $("select.form-control.category").chosen({
                width: "100%",
                disable_search: true,
                placeholder_text_multiple: lang.categories
            });
        }
        
        function get_monthly_costs($select){
            
            var $category_costs = $select.parent().parent().find('.category_costs');
            
            $category_costs.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            
            $.ajax({
                url: jsObject.get_category_costs,
                method: 'GET',
                data: {'category': $select.val()},
                success: function (response) {
                    var sum = '';
                    if (response['value'] > 0) {
                        sum = response['value'] + ' ' + i18n.currency;
                    }else{
                        sum = '-';
                    }
                    $category_costs.html(sum);
                },
                error: function (data) {
                    alert(data);
                }
            });
        }

    });
})(jQuery);
