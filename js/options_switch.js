jQuery(document).ready(function($){
    $('#yarpp-display-mode-save').on('click',function(e){
        e.preventDefault();
        var url  = $(this).attr('href'),
            data = {ypsdt : true, types : []};

        $(this).after($('<span class="spinner"></span>'));

        $i = 0;
        $('input','#yarpp-display-mode').each(function(idx,val){
            if(val.checked) {
                data.types[$i] = val.value;
                $i++;
            }
        });

        $.get(url,data,function(resp){
            setTimeout(function(){
                if(resp === 'ok'){
                    $('.spinner','#yarpp-display-mode').remove();
                } else {
                    $('#yarpp-display-mode').append($('<span style="vertical-align: middle" class="error-message">Something went wrong saving your settings. Please refresh the page and try again.</span>'));
                }
            },1000);
        });
    });

});