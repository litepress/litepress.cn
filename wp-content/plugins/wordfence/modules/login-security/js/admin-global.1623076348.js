(function($) {
	window['GWFLS'] = {
		init: function() {
			
		},

		/**
		 * Sends a WP AJAX call, automatically adding our nonce.
		 *
		 * @param string action
		 * @param string|array|object payload
		 * @param function successCallback
		 * @param function failureCallback
		 */
		ajax: function(action, payload, successCallback, failureCallback) {
			if (typeof(payload) == 'string') {
				if (payload.length > 0) {
					payload += '&';
				}
				payload += 'action=' + action + '&nonce=' + GWFLSVars.nonce;
			}
			else if (typeof(payload) == 'object' && payload instanceof Array) {
				// jQuery serialized form data
				payload.push({
					name: 'action',
					value: action
				});
				payload.push({
					name: 'nonce',
					value: GWFLSVars.nonce
				});
			}
			else if (typeof(payload) == 'object') {
				payload['action'] = action;
				payload['nonce'] = GWFLSVars.nonce;
			}


			$.ajax({
				type: 'POST',
				url: GWFLSVars.ajaxurl,
				dataType: "json",
				data: payload,
				success: function(json) {
					typeof successCallback == 'function' && successCallback(json);
				},
				error: function() {
					typeof failureCallback == 'function' && failureCallback();
				}
			});
		},

		dismiss_notice: function(nid) {
			this.ajax('wordfence_ls_dismiss_notice', {
					id: nid
				},
				function(res) { $('.wfls-notice[data-notice-id="' + nid + '"]').fadeOut(); },
				function() { $('.wfls-notice[data-notice-id="' + nid + '"]').fadeOut(); }
			);
		},
	};

	$(function() {
		GWFLS.init();
	});
})(jQuery);

