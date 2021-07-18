( function( $ ) {

	var lava_ajax_search = function( el ) {
		this.el = $( el );
		this.args = lava_ajax_search_args;

		this.cache().init();
		this.init();
	};

	lava_ajax_search.prototype.constructor = lava_ajax_search;

	lava_ajax_search.prototype._cache = new Array();

	lava_ajax_search.prototype.cache = function() {
		var self = this;
		return {
			clear : function() { self._cache = new Array(); },
			init : function() { this.clear(); },
			add : function( key, value ) { self._cache[ key ] = value; },
			get : function( key ) { return self._cache[ key ] || false; },
			getAll : function() { return self._cache; },
			getResults : function( keyword, callback, failCallback ) {
				var
					obj = this,
					postData = {};
				postData.action = 'lava_ajax_search_query';
				postData.search_term = keyword;

				$.post( self.args.ajaxurl, postData,
					function( xhr ) {
						xhr = $.map( xhr, function( xhrDATA, xhrIDX ) {
							xhrDATA.result_type = 'ajax';
							return new Array( xhrDATA );
						} );

						obj.add( keyword, xhr );

						if( typeof callback == 'function' ) {
							callback( xhr );
						}
					}, 'json'
				).fail( function(){
					if( typeof failCallback == 'function' ) {
						failCallback();
					}
				} );
			}
		}
	}

	lava_ajax_search.prototype.init = function() {
		var self = this;
		self.el.trigger('lava:init/before', self);
		self.setSelectize();
		self.el.trigger('lava:init/after', self);
	}

	lava_ajax_search.prototype.autocomplete = {};
	lava_ajax_search.prototype.autocomplete.select = function() {
		var self = this;
		return function( event, ui ) {
			var target = $( 'a', ui.item.value );
			$( window ).trigger( 'lava:ajax-search-select', ui );
			if( ui.item.type == 'listing_category' ) {
				$( this ).val( ui.item.label );
			}else{
				if( target.length ) {
					window.location = target.attr( 'href' );
					return false;
				}
			}
			return false;
		}
	}

	lava_ajax_search.prototype.autocomplete.focus = function() {
		var self = this;
		return function( event, ui ) {
			$( '.ui-autocomplete li' ).removeClass( 'ui-state-hover' );
			$( '.ui-autocomplete' ).find( 'li:has(a.ui-state-focus)' ).addClass( 'ui-state-hover' );
			return false;
		}
	}

	lava_ajax_search.prototype.autocomplete.render = function() {
		var self = this;
		return function( ul, item ) {
			var icon = item.icon || '';
			// var html = $( item.value );
			var html = item.value;
			var is_ajax = item.result_type == 'ajax' ? 'show-result' : '';

			ul.addClass( 'lava_ajax_search' ).css( 'zIndex', 1 );
			if (item.type_label != "") {
				$(ul).data("current_cat", item.type)
				return $("<li>").attr("class", 'type-' + item.type + " group-title " + is_ajax ).append( "<span>" + item.value + "</span>").appendTo(ul);
			} else {
				/*
				return $("<li>").attr("class", 'type-' + item.type + " group-content " + is_ajax ).append( "<a class='x'>" + item.value + "</a>").appendTo(ul); */
				return $("<li>").attr("class", 'type-' + item.type + " group-content " + is_ajax ).append( item.value).appendTo(ul);
			}
		}
	}

	lava_ajax_search.prototype.setSelectize = function() {
		var
			self = this,
			form = this.el,
			args = this.args,
			element = $( 'input[data-search-input]', form ),
			submit = $( 'button', form );

		var show_category = 'yes' == args.show_category;
		var categories = JSON.parse( args.listing_category );

		if( 0 < element.length && typeof $.fn.autocomplete != 'undefined' ) {
			element.autocomplete({
				source: function( request, response ) {
					var results;
					if( request.term ) {
						results = self.cache().get( request.term );
						if( results ) {
							if(show_category){
								results = $.extend(true, {}, results, categories);
							}
							response( results );
							return;
						}

						form.addClass( 'ajax-loading' );
						$( window ).trigger( 'lava:ajax-search-before-send', form );

						self.cache().getResults( request.term, function( xhr ) {
							if(show_category){
								xhr = $.extend(true, {}, xhr, categories);
							}
							response(xhr);
							form.removeClass( 'ajax-loading' );
							$( window ).trigger( 'lava:ajax-search-complete', form );
						}, response );

					}else{
						response( categories );
					}
				},
				minLength: args.min_search_length,
				select: self.autocomplete.select(),
				focus: self.autocomplete.focus(),
				open: function() {
					$( '.lava_ajax_search' ).outerWidth( element.outerWidth() ).css( 'z-index', 5001 );
					$( window ).trigger( 'lava:ajax-search-open', { 'form' : form, 'element' : element });
				},
			}).on( 'focus', function() {
				$( this ).autocomplete( 'search', $( this ).val() );
			}).data("ui-autocomplete")._renderItem = self.autocomplete.render();
		}
	}
	$.lava_ajax_search = function() {
		$( ".lava-ajax-search-form-wrap" ).each( function() {
			if( !$( this ).data( 'las-instance') ) {
				$( this ).data( 'las-instance', new lava_ajax_search( this ) );
			}
		} );
	}
	$.lava_ajax_search();
} )( jQuery );