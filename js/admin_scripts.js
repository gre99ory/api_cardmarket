jQuery(document).ready(function($){

    let AppMkm = {
        proc: 0,
        state: 'evaluated',
        dateFrom: '',
        dateTo: '',
        selectApp: '',
        moreArticles: $('#mkm-api-show-more-articles').html(),
        moreArticlesStep: 30
    }

    $(document).on('click', '#more-articles', function(){
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action:'mkm_api_ajax_more_articles',
                data : AppMkm.moreArticles,
                start: AppMkm.moreArticlesStep
            },
            beforeSend: function(){
                AppMkm.moreArticles -= 30;
                AppMkm.moreArticlesStep += 30;
                if(AppMkm.moreArticles <= 0) $('#more-articles').hide();
            },
            success: function(result){
                if ( result != 'done') {
                    $('#mlm-api-article-wrap').append(result);
                    $('#mkm-api-show-more-articles').html(AppMkm.moreArticles);
                }
            }
        });
    });

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

    $('#mkm-api-filter-select-state-id').on('change', function(){
        get_list_data_orders();
    });

    function get_list_data_orders(){
        $('.mkm-api-show-more-list-orders button').show();
        $('.mkm-api-show-more-list-orders button').data('start', 30);
        let from  = $('#mkm-api-filter-date-from').attr('value');
        let to    = $('#mkm-api-filter-date-to').attr('value');
        let app   = $('#mkm-api-filter-select-app-id').attr('value');
        let state = $('#mkm-api-filter-select-state-id').attr('value');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action:'mkm_api_ajax_get_orders',
                app : app,
                from: from,
                to: to,
                state: state
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
        let from  = $('#mkm-api-filter-date-from').attr('value');
        let to    = $('#mkm-api-filter-date-to').attr('value');
        let app   = $('#mkm-api-filter-select-app-id').attr('value');
        let state = $('#mkm-api-filter-select-state-id').attr('value');
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action:'mkm_api_ajax_get_orders',
                start: $(this).data('start'),
                from: from,
                to: to,
                app: app,
                state: state,
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
        mkm_api_get_ajax_data(1, 1, key, 0);
    });

    function mkm_api_get_ajax_data(data, count, key, state ){
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                data: data,
                count: count,
                key: key,
                state: state,
                action: 'mkm_api_ajax_data',
            },
            beforeSend: function(){
                $('#content-for-modal').css({'display':'flex'});
                if(count == 1){
                    $('#content-for-modal .mkm-api-progress').css({'width': AppMkm.proc});
                    $('#content-for-modal .proc').html(AppMkm.proc + '%');
                } else {
                    if (state == 3) {
                        AppMkm.proc = ( Math.round(data/(count/100)) >= 100 ) ? 100 : Math.round(data/(count/100));
                        $('#content-for-modal .mkm-api-progress').css({'width': AppMkm.proc + '%'});
                        $('#content-for-modal .proc').html(AppMkm.proc + '%');
                    }
                }
            },
            success: function(result){
                console.log(result);
                if (result && result != 'end'){
                    let res = JSON.parse(result);
                    setTimeout(function(){
                        mkm_api_get_ajax_data(res.data, res.count, res.key, res.state);
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

    function mkmApiAjaxUpdateOrders ( key, state, count ){
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'mkm_api_ajax_update_orders',
                key: key,
                state: state,
                count: count,
            },
            beforeSend: function(){
                $('.mkm-api-update-orders').attr('disabled',true).find('.mkm-api-update-orders-span').addClass('rotates');
            },
            success: function(result){
                console.log(result);
                if(result != 'done'){
                    res = JSON.parse(result);
                    mkmApiAjaxUpdateOrders(res.key, res.state, res.count);
                } else {
                    $('.mkm-api-update-orders').attr('disabled',false).find('.mkm-api-update-orders-span').removeClass('rotates');
                }
            }
        });
    }

    $(document).on('click', '.mkm-api-checkup', function(){
        let check = $(this).data('check');
        let key = $(this).data('key');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                check: check,
                data: key,
                action: 'mkm_api_checkup',
            },
            success: function(result){
                console.log(result);
            }
        });

    });

    $(document).on('click', '.mkm-api-update-orders', function(e){
        e.preventDefault();
        mkmApiAjaxUpdateOrders($(this).data('key'), 0, 1);
    });

});