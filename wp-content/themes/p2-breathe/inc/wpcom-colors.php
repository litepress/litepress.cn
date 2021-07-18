<?php

$options = get_theme_mod( 'breathe_theme_options' );
$alternate_color = ( !empty( $options['alternate_color'] ) && '#3498db' != $options['alternate_color'] ? $options['alternate_color'] : '#3498db' );
$link_color = ( !empty( $options['link_color'] ) && '#3498db' != $options['link_color'] ? $options['link_color'] : '#3498db' );

add_color_rule( 'bg', '#f1f1f1', array(
 	array( 'body', 'background-color' ),
 	array( 'body.custom-background', 'background-color' ),
 	array( '.o2-app-footer > .navigation, .navigation .nav-older a', 'border-color', 'bg', 2 ),
 	array( '#secondary', 'background' ),
) );

add_color_rule( 'link', $link_color, array(
	array( 'a', 'color', '#ffffff' ),
	array( 'a:visited', 'color', '#ffffff' ),
	array( 'a:active', 'color', '#ffffff' ),
	array( '.entry-meta .entry-actions:hover a', 'color', '#ffffff' ),
	array( '.entry-meta .entry-actions:hover a:visited', 'color', '#ffffff' ),
	array( '.comment-meta .comment-actions:hover a', 'color', '#ffffff' ),
	array( '.comment-meta .comment-actions:hover a:visited', 'color', '#ffffff' ),
	array( '#help dt', 'color', '#ffffff' ),
	array( '#media-buttons .button', 'color', '#ffffff' ),
	array( '.o2 .o2-editor-toolbar-button', 'color', '#fafafa' ),
	array( '.o2-comment:hover .o2-actions a:after', 'color', '#ffffff' ),
	array( '.entry-content a', 'color', '#ffffff' ),
	array( '.entry-meta a', 'color', '#ffffff' ),
	array( '.o2-comment a', 'color', '#ffffff' ),
	array( '.widget a', 'color', '#ffffff' ),
	array( '.widget li a', 'color', '#ffffff' ),
	array( '.site-main a', 'color', '#ffffff' ),
	array( '.o2 .o2-app-controls a', 'color', 'fg1' ),
	array( '.navigation-main a', 'color', '#ffffff', 7 ),
	array( '.navigation-main ul li li a', 'color', '#ffffff' ),
	array( '.navigation-main ul li:hover > a, .navigation-main ul li li a:hover, .navigation-main ul ul li:hover > a, .navigation-main ul li.current-menu-item a', 'color', 'link', 7 ),
	array( '.entry-content p a', 'color', '#ffffff' ),
	array( '.navigation a:hover', 'color', '#ffffff' ),
	array( '.o2-app-footer a, .site-footer a', 'color', 'bg' ),
	array( '.navigation-main ul li:hover > a', 'background-color' ),
	array( '.navigation-main ul ul li:hover > a', 'background-color' ),
	array( '.navigation-main ul li.current-menu-item a', 'background-color' ),
	array( '#o2-expand-editor', 'background' ),
	array( '.o2 .o2-editor-wrapper.dragging', 'outline-color' ),
	array( '.o2-editor-upload-progress', 'background-color' ),
	array( 'li.o2-filter-widget-item a', 'color' ),
	array( '.o2-app-new-post a.o2-editor-fullscreen', 'color', 'bg' )
) );

add_color_rule( 'fg1', $alternate_color, array(
	array( '.o2 .o2-app-page-title', 'background-color' ),
	array( 'li.o2-filter-widget-item a.o2-filter-widget-selected', 'background-color' ),
	array( '.o2 .o2-app-new-post h2', 'background-color', 'color' ),
	array( 'h1.site-title a', 'color' ),
	array( 'h1.widget-title', 'color', '#ffffff' ),
	array( 'li.o2-filter-widget-item a:before', 'color' ),
) );

add_color_rule( 'fg2', '#ffffff', array(
	array( '.o2 .o2-app-page-title', 'color', 'fg1' ),
	array( '.o2 .o2-app-new-post h2', 'color', 'fg1' ),
	array( 'li.o2-filter-widget-item a.o2-filter-widget-selected,li.o2-filter-widget-item a.o2-filter-widget-selected:before', 'color', 'fg1' ),

) );

add_color_rule( 'extra', '#000000', array(
	array( '.no-sidebar .site-header .site-title a', 'color', 'bg' ),

) );

add_color_rule( 'extra', '#222222', array(
	array( '.no-sidebar .site-header .site-description', 'color', 'bg' ),
	array( 'a.subscription-link.o2-follow.post-comments-subscribed:after', 'color' ),
	array( '.o2-post:hover a.subscription-link.o2-follow.post-comments-subscribed:after', 'color' ),
	array( '.o2-app-new-post .oblique-strategy', 'color', 'bg', 4 )
) );

add_color_rule( 'extra', '#555555', array(
	array( '.site-footer, .o2-app-new-post .comment-subscription-form', 'color', 'bg' )
) );


add_theme_support( 'custom_colors_extra_css', 'breathe_extra_css' );
function breathe_extra_css() { ?>
.custom-background.o2 .tag-p2-xpost {
	background-color: rgba(255,255,255,0.9) !important;
}
<?php
}

add_color_palette( array(
    '#f4f4f4',
    '',
    '#f0e5c9',
    '#a68c69',
    '#594433',
), 'Neutral' );

add_color_palette( array(
    '#2b2b2b',
    '',
    '#bcbcbc',
    '#424242',
    '#e9e9e9',
), 'Dark' );

add_color_palette( array(
    '#f1f1f1',
    '',
    '#3498db',
    '#888888',
    '#eeeeee',
), 'Light' );
