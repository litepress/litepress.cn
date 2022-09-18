/**
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YIT Plugin Framework
 */

jQuery(
	function ( $ ) {
		$( document ).on(
			'click',
			'.notice-dismiss',
			function () {
				var t          = $( this ),
					wrapper_id = t.parent().attr( 'id' );

				if ( wrapper_id === 'yith-system-alert' ) {
					var cname  = 'hide_yith_system_alert',
						cvalue = 'yes';

					document.cookie = cname + "=" + cvalue + ";path=/";
				}
			}
		);
		$( document ).on(
			'click',
			'.yith-download-log',
			function () {

				var container = $( this ).parent();
				var data      = {
					action: 'yith_create_log_file',
					file  : $( this ).data( 'file' ),
				};

				container.addClass( 'progress' );

				$.post(
					yith_sysinfo.ajax_url,
					data,
					function ( response ) {
						if ( false !== response.file ) {
							var a        = document.createElement( "a" );
							var fileName = response.file.split( "/" ).pop();
							a.href       = response.file;
							a.download   = fileName;
							document.body.appendChild( a );
							a.click();
							window.URL.revokeObjectURL( response.file );
							a.remove();
						}
						container.removeClass( 'progress' );
					}
				);
			}
		);
		$( document ).on(
			'click',
			'.copy-link',
			function ( e ) {
				e.preventDefault();

				var $this = $( this ),
					$temp = $( '<textarea>' );

				$( 'body' ).append( $temp );
				$temp.val( "define( 'WP_DEBUG', true );\ndefine( 'WP_DEBUG_LOG', true );\ndefine( 'WP_DEBUG_DISPLAY', false );" ).select();
				document.execCommand( "Copy" );
				$temp.remove();
				if ( ! $this.find( '.copied-tooltip' ).length ) {
					$this
						.append(
							$( '<span/>', {class: 'copied-tooltip'} )
								.html( $this.data( "tooltip" ) ).fadeIn( 300 )
						);
					setTimeout(
						function () {
							$this.find( ".copied-tooltip" ).fadeOut().remove()
						},
						3000
					);
				}

			}
		);
	}
);
