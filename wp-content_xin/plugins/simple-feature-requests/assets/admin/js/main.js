(function( $, document ) {

    var jck_sfr = {

        on_ready: function() {
			jck_sfr.setup_attachments();
		},


        /**
         * Attachments setup
         */
        setup_attachments: function() {

            var wrapper         = $('.jck-sfr-attachments');

            wrapper.on('click', '.jck-sfr-remove_attachment', function(){
                var attachment_id = $(this).parent().data('attachment_id');
                var values        = $('[name="jck_sfr_attachments"]').val();
                var ids           = values ? values.split(',') : [];

                $(this).parent().remove();

                ids = ids.filter( function(id){
                    return id != attachment_id;
                });

                $('[name="jck_sfr_attachments"]').val( ids.join(',') );
            });


            var attachment_frame;

            $('.jck-sfr-add_attachments').on('click', function(e){

                e.preventDefault();

                if( attachment_frame ){
                    attachment_frame.open();
                    return;
                }

                attachment_frame = wp.media.frames.product_gallery = wp.media({
                    title: jck_sfr_vars.il8n.choose_attachments,
                    button: {
                        text: jck_sfr_vars.il8n.add_attachments
                    },
                    states: [
                        new wp.media.controller.Library({
                            title: jck_sfr_vars.il8n.choose_attachments,
                            filterable: 'all',
                            multiple: true
                        })
                    ]
                });

                attachment_frame.on( 'select', function() {
                    var selection      = attachment_frame.state().get( 'selection' );
                    var attachment_ids = $('[name="jck_sfr_attachments"]').val();
                    var values         = attachment_ids ? attachment_ids.split(',') : [];

                    selection.map( function( attachment ) {
                        attachment = attachment.toJSON();

                        if ( attachment.id ) {
                            var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                            wrapper.append(
                                '<li class="jck-sfr-attachment" data-attachment_id="' + attachment.id + '"><img src="' + attachment_image +
                                '" /><a href="#" class="jck-sfr-remove_attachment"></a></li>'
                            );

                            values.push( attachment.id );
                        }
                    });

                    $('[name="jck_sfr_attachments"]').val( values.join(',') );
                });

                attachment_frame.open();

            });
        }
    };

    $( document ).ready( jck_sfr.on_ready );

}( jQuery, document ));