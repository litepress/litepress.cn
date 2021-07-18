/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

jQuery( function ( $ ) {
    var wrap    = $( '.yith-plugin-fw-wp-page-wrapper' ),
        notices = $( 'div.updated, div.error, div.notice' );

    // prevents the WC message for changes when leaving the panel page
    $( '.yith-plugin-fw-wp-page-wrapper .woo-nav-tab-wrapper' ).removeClass( 'woo-nav-tab-wrapper' ).addClass( 'yith-nav-tab-wrapper' );

    // prevent moving notices withing the tab in WP Pages and move them into the wrapper
    notices.addClass( 'inline' );
    if ( wrap.length ) {
        wrap.prepend( notices );
    }

} );