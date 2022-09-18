/**
 * Created by Your Inspiration on 15/04/2015.
 */

jQuery(document).ready(function($){
    var initializeMap = function( gmap ){

        var zoom_map=gmap.data('zoom'),
            addr=gmap.data('address');
        gmap.gmap3({
            map   : {
                options: {
                    zoom             : zoom_map,
                    disableDefaultUI         : true,
                    mapTypeControl           : false,
                    panControl               : false,
                    zoomControl              : false,
                    scaleControl             : false,
                    streetViewControl        : false,
                    rotateControl            : false,
                    rotateControlOptions     : false,
                    overviewMapControl       : false,
                    overviewMapControlOptions: false
                },
                address: addr
            },
            marker: {
                address: addr
            }
        });
    };

    $('.tab-faqs-container .tab-faq-wrapper').click(function () {
        var text = $(this).find('div.tab-faq-item');

        if (text.is(':hidden')) {
            text.slideDown('200');
            $(this).find('span').addClass('opened').removeClass('closed');
        } else {
            text.slideUp('200');
            $(this).find('span').addClass('closed').removeClass('opened');
        }

    });
    /*MAP*/

    $('.ywtm_map').each(function(){

        var parent = $(this).parent().parent();

        if( parent.css('display') == 'block')
            initializeMap( $( this ));
    });

    $('.woocommerce-tabs ul.tabs li a').on( 'click', function(  ) {
        var tab = $(this),
            tabs_wrapper = tab.closest( '.woocommerce-tabs'),
            tabcon = $( 'div' + tab.attr( 'href' ), tabs_wrapper);

        if( tabcon.find('.ywtm_map').length > 0 ){

            var map = tabcon.find('.ywtm_map');

            initializeMap( map );
         }
    });

    $('.ywtm_btn_sendmail').on('click',function(e){
        e.preventDefault();

        var form = $(this).closest('form.ywtm_contact_form'),
            params = form.serialize(),
            block_params = {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                },
                ignoreIfBlocked: true
            };

        form.block(block_params);
        $.ajax({
           type:'POST',
            data : params+'&action='+ywtm_params.action.ywtm_sendermail,
            url: ywtm_params.admin_url,
            success:function(response){
                form.unblock();
                form.parent().find('.error_messages').html(response);
                form.get(0).reset()
            }
        });

    });

    

});
