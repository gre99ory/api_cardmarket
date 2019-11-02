jQuery(document).ready(function($){
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