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
});