jQuery(document).ready(function($){

    $('#mkm-api-show-more').html($('.mkm-api-filter-count').data('count') - 30);

    if($('.mkm-api-filter-count').data('count') <= 30 ){
        $('#mkm-api-show-more').hide();
    }

    $( "#mkm-api-filter-date-from" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: 'yy-mm-dd'
    }).on('change', function(){
        get_list_data_orders();
    });
    $( "#mkm-api-filter-date-to" ).datepicker({
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: 'yy-mm-dd'
    }).on('change', function(){
        get_list_data_orders();
    });

    $('#mkm-api-filter-select-app-id').on('change', function(){
        get_list_data_orders();
    });

    function get_list_data_orders(){
        $('.mkm-api-show-more-list-orders button').show();
        $('.mkm-api-show-more-list-orders button').data('start', 30);
        let now = new Date;
        let from_elem = $('#mkm-api-filter-date-from').attr('value');
        let to_elem = $('#mkm-api-filter-date-to').attr('value');
        let app = $('#mkm-api-filter-select-app-id').attr('value');
        let from = from_elem != '' ? new Date (from_elem) : new Date ('1970-01-01');
        let to = to_elem != '' ? new Date (to_elem) : now;
        from.setUTCHours(0, 0, 0, 900);
        to.setUTCHours(23, 59, 59, 900)
        from_s = from.getTime();
        to_s = to.getTime();
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action:'mkm_api_ajax_get_orders',
                app : app,
                from: from_s,
                to: to_s
            },
            beforeSend: function(){
                $('.mkm-api-orders-table tbody').css({'opacity': 0})
                $('.mkm-api-loader').show();
                $('.mkm-api-show-more-list-orders').hide();
            },
            success: function(result){
                if(result != 'no_data') {
                    let html = '<tr class="mkm-api-list-orders">' + $('.mkm-api-orders-table tr:first-child').html() + '</tr>';
                    let res = JSON.parse(result);
                    if(res.count > 30 ) {
                        $('#mkm-api-show-more').text(res.count - 30)
                        $('.mkm-api-show-more-list-orders').show();
                    }
                    $('.mkm-api-data-count').html(res.count).data('count', res.count);
                    html += res.html;
                    $('.mkm-api-orders-table').html(html);
                    $('.mkm-api-loader').hide();
                    $('.mkm-api-orders-table tbody').css({'opacity': 1})
                }
            }
        });
    }

    $(document).on('click', '.mkm-api-show-more-list-orders button', function(e){
        e.preventDefault();
        let now = new Date;
        let from_elem = $('#mkm-api-filter-date-from').attr('value');
        let to_elem = $('#mkm-api-filter-date-to').attr('value');
        let app = $('#mkm-api-filter-select-app-id').attr('value');
        let from = from_elem != '' ? new Date (from_elem) : new Date ('1970-01-01');
        let to = to_elem != '' ? new Date (to_elem) : now;
        from.setUTCHours(0, 0, 0, 900);
        to.setUTCHours(23, 59, 59, 900)
        from_s = from.getTime();
        to_s = to.getTime();
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action:'mkm_api_ajax_get_orders',
                start: $(this).data('start'),
                from: from_s,
                to: to_s,
                app: app,
            },
            beforeSend: function(){

            },
            success: function(result){
                let res = JSON.parse(result);
                $('.mkm-api-orders-table').append(res.html);
                $('.mkm-api-show-more-list-orders button').data('start', res.start);
                $('#mkm-api-show-more').html(res.count - res.start);
                if(res.count - res.start <= 0) {
                    $('.mkm-api-show-more-list-orders button').hide();
                }
            }
        });
    });

    $(document).on('click', '.mkm-api-delete-key a', function(){
        let elem = $(this);
        let data = elem.data('key');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                data: data,
                action: 'mkm_api_delete_key',
            },
            beforeSend: function(){

            },
            success: function(result){
                console.log(result);
                if(result == 1){
                        elem.parents('.mkm-api-key-row').fadeOut(300, function(){
                        elem.parents('.mkm-api-key-row').remove();
                    });
                }
            }
        });
        return false;
    });

    $(document).on('click', '.mkm-api-get-all-data', function(e){
        e.preventDefault();
        let key = $(this).data('key');
        mkm_api_get_ajax_data(1, 1, key);
    });

    function mkm_api_get_ajax_data(data, count, key ){
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                data: data,
                count: count,
                key: key,
                action: 'mkm_api_ajax_data',
            },
            beforeSend: function(){
                $('#content-for-modal').css({'display':'flex'});
                if(count == 1){
                    $('#content-for-modal .mkm-api-progress').css({'width': 0});
                    $('#content-for-modal .proc').html('0%');
                } else {
                    $('#content-for-modal .mkm-api-progress').css({'width': data/(count/100) + '%'});
                    $('#content-for-modal .proc').html(Math.round(data/(count/100)) + '%');
                }
            },
            success: function(result){
                console.log(result);
                if (result && result != 'end'){
                    let res = JSON.parse(result);
                    setTimeout(function(){
                        mkm_api_get_ajax_data(res.data, res.count, res.key);
                    }, 2000);
                } else {
                    window.location.reload()
                }
            }
        });
    }

    $(document).on('change', '.mkm-api-cron-select', function(){
        let data = $(this).attr('value');
        let key = $(this).data('key');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                data: data,
                key: key,
                action: 'mkm_api_change_cron_select',
            },
            beforeSend: function(){

            },
            success: function(result){
                console.log('change cron');
            }
        });
    });

});