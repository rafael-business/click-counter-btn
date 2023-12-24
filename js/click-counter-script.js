jQuery(document).ready(function($) {
    $('#click-counter-btn').on('click', function(e) {
        e.preventDefault();

        let w = $(e.target).outerWidth();
        let text = $(e.target).find('span').html();
        
        $('.load').fadeIn(250);
        $(e.target).css({width: w});
        $(e.target).addClass('disabled');
        $(e.target).find('span').html('');
        $('#click-result').hide();

        $.ajax({
            url: click_counter_vars.api_url,
            type: 'POST',
            success: function(response) {
                let load_class = response.success ? 'check' : 'error';
                let resu_class = response.success ? 'success' : 'alert';
                setTimeout(() => {
                    $('.load').addClass( load_class );
                }, 1000);
                setTimeout(() => {
                    $('.load').removeClass( load_class );
                    $('.load').hide();
                    $(e.target).removeClass('disabled');
                    $(e.target).find('span').html(text);

                    $('#click-result').html( response.message );
                    $('#click-result').addClass( resu_class );
                    $('#click-result').fadeIn();
                }, 2000);
                setTimeout(() => {
                    window.open( $(e.target).attr('href') );
                }, 3000);
            },
            error: function(error) {
                console.log(error);
                $('.load').addClass( 'error' );
                $('#click-result').html( 'Ajax error. Please contact us.' );
                $('#click-result').addClass( 'ajax-error' );
                $('#click-result').fadeIn();
            }
        });
    });
});
