(function($) {
    // woof_checkbox_count
    $(document).ready(() => {
        $('.woof_checkbox_count').each((i, item) => {
            if ($(item).text() === '(0)') {
                $(item).closest('li').css('display', 'none');
                $(item).closest('li').addClass('no-products');
            }
        });

        let displayedItems = 0;
        $('.woof_container_pa_size .woof_list li').each((i, item) => {
            if (!$(item).hasClass('no-products')) {
                if (displayedItems <= 6) {
                    ++displayedItems;
                    $(item).removeClass('woof_hidden_term');
                }
            }
        })
        
        $('#billing_city_text').val($('#billing_city').val()).trigger('change');
        $('#billing_city').on('change', () => {
            $('#billing_city_text').val($('#billing_city').val()).trigger('change');
            $(document.body).trigger('update_checkout');
        })

        $('#billing_country').on('change', function() {
            const billElement = $('#billing_city');
            const billText = $('#billing_city_text');
            const billSelect = $('#billing_city_select');
            if ($(this).val() === 'KW') {
                if(billElement.attr('type') === 'text'){
                    billElement.remove();
                    billSelect.clone().attr({id: 'billing_city', name: 'billing_city'}).appendTo('#billing_city_field');                   
                }
            }else if($(this).val() !== 'KW'){
                billElement.remove();
                billText.clone().attr({ id: 'billing_city', name: 'billing_city' }).appendTo('#billing_city_field');                   
            }
            billText.val(billElement.val()).trigger('change');
            $(document).trigger('ready');
            $('#billing_city').on('change', () => {
                $(document.body).trigger('update_checkout');
            })
        })

        

    });
})(jQuery)
