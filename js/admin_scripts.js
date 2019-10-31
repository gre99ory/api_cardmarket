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
                if(result == 1){
                    elem.parents('.mkm-api-key-row').fadeOut(300, function(){
                        elem.parents('.mkm-api-key-row').remove();
                    });
                }
            }
        });
        return false;
    });

    $(document).on('click', '#mkm-api-add-api', function(e){
        e.preventDefault();

        mkm_api_get_ajax_data(1, 1);

    });

    function mkm_api_get_ajax_data(data, count ){
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                data: data,
                count: count,
                action: 'mkm_api_ajax_data',
            },
            beforeSend: function(){

            },
            success: function(result){
                if (result && result != 'end'){
                    let res = JSON.parse(result);
                    console.log(res);
                    setTimeout(function(){
                        mkm_api_get_ajax_data(res.data, res.count);
                    }, 5000);
                } else {
                    console.log('end');
                }
            }
        });
    }
});