jQuery(document).ready(function($) {

    tinymce.create('tinymce.plugins.genericond', {
        init : function(ed, url) {
                // Register command for when button is clicked
                ed.addCommand('genericond_insert_shortcode', function() {
                    selected = tinyMCE.activeEditor.selection.getContent();

                    if( selected ){
                        //If text is selected when button is clicked
                        //Wrap shortcode around it.
                        content =  '[genericon icon='+selected+']';
                    } else {
                        content =  '[genericon icon=SOMETHING]';
                    }

                    tinymce.execCommand('mceInsertContent', false, content);
                });

            // Register buttons - trigger above command when clicked
            ed.addButton('genericond_button', {title : 'Insert shortcode', cmd : 'genericond_insert_shortcode', image: url + '/tinymce.png' });
        },   
    });

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('genericond_button', tinymce.plugins.genericond);
});