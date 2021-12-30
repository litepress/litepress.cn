(function($) {
	window['WFLS'] = {
		panelIsOpen: false,
		basePageName: '',
		panelQueue: [],
		pendingChanges: {},
		
		//Screen sizes
		SCREEN_XS: 'xs',
		SCREEN_SM: 'sm',
		SCREEN_MD: 'md',
		SCREEN_LG: 'lg',

		init: function() {
			this.basePageName = document.title;

			var tabs = $('.wfls-page-tabs').find('.wfls-tab a');
			if (tabs.length > 0) {
				tabs.click(function() {
					$('.wfls-page-tabs').find('.wfls-tab').removeClass('wfls-active');
					$('.wfls-tab-content').removeClass('wfls-active');

					var tab = $(this).closest('.wfls-tab');
					tab.addClass('wfls-active');
					var content = $('#' + tab.data('target'));
					content.addClass('wfls-active');
					document.title = tab.data('pageTitle') + " \u2039 " + WFLS.basePageName;
					$(window).trigger('wfls-tab-change', [tab.data('target')]);
				});
				if (window.location.hash) {
					var hashes = WFLS.parseHashes();
					var hash = hashes[hashes.length - 1];
					for (var i = 0; i < tabs.length; i++) {
						if (hash == $(tabs[i]).closest('.wfls-tab').data('target')) {
							$(tabs[i]).trigger('click');
						}
					}
				}
				else {
					$(tabs[0]).trigger('click');
				}
				$(window).on('hashchange', function () {
					var hashes = WFLS.parseHashes();
					var hash = hashes[hashes.length - 1];
					for (var i = 0; i < tabs.length; i++) {
						if (hash == $(tabs[i]).closest('.wfls-tab').data('target')) {
							$(tabs[i]).trigger('click');
						}
					}
				});
			}

			//On/Off Option
			$('.wfls-option.wfls-option-toggled .wfls-option-checkbox').each(function() {
				$(this).on('keydown', function(e) {
					if (e.keyCode == 32) {
						e.preventDefault();
						e.stopPropagation();

						$(this).trigger('click');
					}
				});

				$(this).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var optionElement = $(this).closest('.wfls-option');
					if (optionElement.hasClass('wfls-option-premium') || optionElement.hasClass('wfls-disabled')) {
						return;
					}

					var option = optionElement.data('option');
					var value = false;
					var isActive = $(this).hasClass('wfls-checked');
					if (isActive) {
						$(this).removeClass('wfls-checked').attr('aria-checked', 'false');
						value = optionElement.data('disabledValue');
					}
					else {
						$(this).addClass('wfls-checked').attr('aria-checked', 'true');
						value = optionElement.data('enabledValue');
					}

					var originalValue = optionElement.data('originalValue');
					if (originalValue == value) {
						delete WFLS.pendingChanges[option];
					}
					else {
						WFLS.pendingChanges[option] = value;
					}

					$(optionElement).trigger('change', [false]);
					WFLS.updatePendingChanges();
				});

				$(this).parent().find('.wfls-option-title').on('click', function(e) {
					var links = $(this).find('a');
					var buffer = 10;
					for (var i = 0; i < links.length; i++) {
						var t = $(links[i]).offset().top;
						var l = $(links[i]).offset().left;
						var b = t + $(links[i]).height();
						var r = l + $(links[i]).width();

						if (e.pageX > l - buffer && e.pageX < r + buffer && e.pageY > t - buffer && e.pageY < b + buffer) {
							return;
						}
					}
					$(this).parent().find('.wfls-option-checkbox').trigger('click');
				}).css('cursor', 'pointer');
			});

			//On/Off Boolean Switch Option
			$('.wfls-option.wfls-option-toggled-boolean-switch .wfls-boolean-switch').each(function() {
				$(this).on('keydown', function(e) {
					if (e.keyCode == 32) {
						e.preventDefault();
						e.stopPropagation();

						$(this).trigger('click');
					}
				});

				$(this).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					$(this).find('.wfls-boolean-switch-handle').trigger('click');
				});

				$(this).find('.wfls-boolean-switch-handle').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var optionElement = $(this).closest('.wfls-option');
					if (optionElement.hasClass('wfls-option-premium') || optionElement.hasClass('wfls-disabled')) {
						return;
					}

					var switchElement = $(this).closest('.wfls-boolean-switch');
					var option = optionElement.data('option');
					var value = false;
					var isActive = switchElement.hasClass('wfls-active');
					if (isActive) {
						switchElement.removeClass('wfls-active').attr('aria-checked', 'false');
						value = optionElement.data('disabledValue');
					}
					else {
						switchElement.addClass('wfls-active').attr('aria-checked', 'true');
						value = optionElement.data('enabledValue');
					}

					var originalValue = optionElement.data('originalValue');
					if (originalValue == value) {
						delete WFLS.pendingChanges[option];
					}
					else {
						WFLS.pendingChanges[option] = value;
					}

					$(optionElement).trigger('change', [false]);
					WFLS.updatePendingChanges();
				});

				$(this).parent().find('.wfls-option-title').on('click', function(e) {
					var links = $(this).find('a');
					var buffer = 10;
					for (var i = 0; i < links.length; i++) {
						var t = $(links[i]).offset().top;
						var l = $(links[i]).offset().left;
						var b = t + $(links[i]).height();
						var r = l + $(links[i]).width();

						if (e.pageX > l - buffer && e.pageX < r + buffer && e.pageY > t - buffer && e.pageY < b + buffer) {
							return;
						}
					}
					$(this).parent().find('.wfls-boolean-switch-handle').trigger('click');
				}).css('cursor', 'pointer');
			});

			//On/Off Segmented Option
			$('.wfls-option.wfls-option-toggled-segmented [type=radio]').each(function() {
				$(this).on('click', function(e) {
					var optionElement = $(this).closest('.wfls-option');
					if (optionElement.hasClass('wfls-option-premium') || optionElement.hasClass('wfls-disabled')) {
						return;
					}

					var option = optionElement.data('option');
					var value = this.value;

					var originalValue = optionElement.data('originalValue');
					if (originalValue == value) {
						delete WFLS.pendingChanges[option];
					}
					else {
						WFLS.pendingChanges[option] = value;
					}

					$(optionElement).trigger('change', [false]);
					WFLS.updatePendingChanges();
				});
			});

			//On/Off Multiple Option
			$('.wfls-option.wfls-option-toggled-multiple .wfls-option-checkbox').each(function() {
				$(this).on('keydown', function(e) {
					if (e.keyCode == 32) {
						e.preventDefault();
						e.stopPropagation();

						$(this).trigger('click');
					}
				});

				$(this).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var optionElement = $(this).closest('.wfls-option');
					if (optionElement.hasClass('wfls-option-premium') || optionElement.hasClass('wfls-disabled') || $(this).hasClass('wfls-disabled')) {
						return;
					}

					var checkboxElement = $(this).closest('ul');
					var option = checkboxElement.data('option');
					var value = false;
					var isActive = $(this).hasClass('wfls-checked');
					if (isActive) {
						$(this).removeClass('wfls-checked').attr('aria-checked', 'false');
						value = checkboxElement.data('disabledValue');
					}
					else {
						$(this).addClass('wfls-checked').attr('aria-checked', 'true');
						value = checkboxElement.data('enabledValue');
					}

					var originalValue = checkboxElement.data('originalValue');
					if (originalValue == value) {
						delete WFLS.pendingChanges[option];
					}
					else {
						WFLS.pendingChanges[option] = value;
					}

					$(optionElement).trigger('change', [false]);
					WFLS.updatePendingChanges();
				});

				$(this).parent().find('.wfls-option-title').on('click', function(e) {
					var links = $(this).find('a');
					var buffer = 10;
					for (var i = 0; i < links.length; i++) {
						var t = $(links[i]).offset().top;
						var l = $(links[i]).offset().left;
						var b = t + $(links[i]).height();
						var r = l + $(links[i]).width();

						if (e.pageX > l - buffer && e.pageX < r + buffer && e.pageY > t - buffer && e.pageY < b + buffer) {
							return;
						}
					}
					$(this).parent().find('.wfls-option-checkbox').trigger('click');
				}).css('cursor', 'pointer');
			});

			//Text field option
			$('.wfls-option.wfls-option-text > .wfls-option-content > ul > li.wfls-option-text input').on('change paste keyup', function() {
				var e = this;

				setTimeout(function() {
					var optionElement = $(e).closest('.wfls-option');
					var option = optionElement.data('textOption');

					if (typeof option !== 'undefined') {
						var value = $(e).val();

						var originalValue = optionElement.data('originalTextValue');
						if (originalValue == value) {
							delete WFLS.pendingChanges[option];
						}
						else {
							WFLS.pendingChanges[option] = value;
						}

						$(optionElement).trigger('change', [false]);
						WFLS.updatePendingChanges();
					}
				}, 4);
			});
			
			//Menu option
			$('.wfls-option.wfls-option-toggled-select > .wfls-option-content > ul > li.wfls-option-select select, .wfls-option.wfls-option-select > .wfls-option-content > ul > li.wfls-option-select select, .wf-option.wfls-option-select > li.wfls-option-select select').each(function() {
				if (!$.fn.wfselect2) { return; }

				var width = (WFLS.screenSize(500) ? '200px' : 'resolve');
				if ($(this).data('preferredWidth')) {
					width = $(this).data('preferredWidth');
				}

				$(this).wfselect2({
					minimumResultsForSearch: -1,
					width: width
				}).on('change', function () {
					var optionElement = $(this).closest('.wfls-option');
					var option = optionElement.data('selectOption');
					var value = $(this).val();

					var originalValue = optionElement.data('originalSelectValue');
					if (originalValue == value) {
						delete WFLS.pendingChanges[option];
					}
					else {
						WFLS.pendingChanges[option] = value;
					}

					$(optionElement).trigger('change', [false]);
					WFLS.updatePendingChanges();
				});
			}).triggerHandler('change');

			//Text area option
			$('.wfls-option.wfls-option-textarea > .wfls-option-content > ul > li.wfls-option-textarea textarea').on('change paste keyup', function() {
				var e = this;

				setTimeout(function() {
					var optionElement = $(e).closest('.wfls-option');
					var option = optionElement.data('textOption');
					var value = $(e).val();

					var originalValue = optionElement.data('originalTextValue');
					if (originalValue == value) {
						delete WFLS.pendingChanges[option];
					}
					else {
						WFLS.pendingChanges[option] = value;
					}

					$(optionElement).trigger('change', [false]);
					WFLS.updatePendingChanges();
				}, 4);
			});

			//Switch Option
			$('.wfls-option.wfls-option-switch .wfls-switch > li').each(function(index, element) {
				$(this).on('keydown', function(e) {
					if (e.keyCode == 32) {
						e.preventDefault();
						e.stopPropagation();

						$(this).trigger('click');
					}
				});

				$(element).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var optionElement = $(this).closest('ul.wfls-option-switch, div.wfls-option-switch');
					var optionName = optionElement.data('optionName');
					var originalValue = optionElement.data('originalValue');
					var value = $(this).data('optionValue');

					var control = $(this).closest('.wfls-switch');
					control.find('li').each(function() {
						$(this).toggleClass('wfls-active', value == $(this).data('optionValue')).attr('aria-checked', value == $(this).data('optionValue') ? 'true' : 'false');
					});

					if (originalValue == value) {
						delete WFLS.pendingChanges[optionName];
					}
					else {
						WFLS.pendingChanges[optionName] = value;
					}

					$(optionElement).trigger('change', [false]);
					WFLS.updatePendingChanges();
				});
			});

			//Dropdown/Text Options
			$('select.wfls-option-select, input.wfls-option-input').each(function() {
				$(this).data('original', $(this).val());
			}).on('change input', function(e) {
				var input = $(this);
				var name = input.attr('name');
				var value = input.val();
				var original = input.data('original');
				if (value === original || (input.hasClass('wfls-option-input-required') && value === '')) {
					delete WFLS.pendingChanges[name];
				}
				else {
					WFLS.pendingChanges[name] = value;
				}
				WFLS.updatePendingChanges();
			});

			$('#wfls-save-changes').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				WFLS.saveOptions(function(res) {
					WFLS.pendingChanges = {};
					WFLS.updatePendingChanges();

					if (res.redirect) {
						window.location.href = res.redirect;
					}
					else {
						window.location.reload(true);
					}
				});
			});

			$('#wfls-cancel-changes').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				//On/Off options
				$('.wfls-option.wfls-option-toggled').each(function() {
					var enabledValue = $(this).data('enabledValue');
					var disabledValue = $(this).data('disabledValue');
					var originalValue = $(this).data('originalValue');
					if (enabledValue == originalValue) {
						$(this).find('.wfls-option-checkbox').addClass('wfls-checked').attr('aria-checked', 'true');
					}
					else {
						$(this).find('.wfls-option-checkbox').removeClass('wfls-checked').attr('aria-checked', 'false');
					}
					$(this).trigger('change', [true]);
				});

				$('.wfls-option-toggled-boolean-switch').each(function() {
					var enabledValue = $(this).data('enabledValue');
					var disabledValue = $(this).data('disabledValue');
					var originalValue = $(this).data('originalValue');
					if (enabledValue == originalValue) {
						$(this).find('.wfls-boolean-switch').addClass('wfls-active').attr('aria-checked', 'true');
					}
					else {
						$(this).find('.wfls-boolean-switch').removeClass('wfls-active').attr('aria-checked', 'false');
					}
					$(this).trigger('change', [true]);
				});

				$('.wfls-option.wfls-option-toggled-segmented').each(function() {
					var originalValue = $(this).data('originalValue');
					$(this).find('[type=radio]').each(function() {
						if (this.value == originalValue) {
							this.checked = true;
							return false;
						}
					});
					$(this).trigger('change', [true]);
				});

				//On/Off multiple options
				$('.wfls-option.wfls-option-toggled-multiple').each(function() {
					$(this).find('.wfls-option-checkboxes > ul').each(function() {
						var enabledValue = $(this).data('enabledValue');
						var disabledValue = $(this).data('disabledValue');
						var originalValue = $(this).data('originalValue');
						if (enabledValue == originalValue) {
							$(this).find('.wfls-option-checkbox').addClass('wfls-checked').attr('aria-checked', 'true');
						}
						else {
							$(this).find('.wfls-option-checkbox').removeClass('wfls-checked').attr('aria-checked', 'false');
						}
					});
					$(this).trigger('change', [true]);
				});

				//On/Off options with menu
				$('.wfls-option.wfls-option-toggled-select').each(function() {
					var selectElement = $(this).find('.wfls-option-select select');
					var enabledToggleValue = $(this).data('enabledToggleValue');
					var disabledToggleValue = $(this).data('disabledToggleValue');
					var originalToggleValue = $(this).data('originalToggleValue');
					if (enabledToggleValue == originalToggleValue) {
						$(this).find('.wfls-option-checkbox').addClass('wfls-checked').attr('aria-checked', 'true');
						selectElement.attr('disabled', false);
					}
					else {
						$(this).find('.wfls-option-checkbox').removeClass('wfls-checked').attr('aria-checked', 'false');
						selectElement.attr('disabled', true);
					}

					var originalSelectValue = $(this).data('originalSelectValue');
					$(this).find('.wfls-option-select select').val(originalSelectValue).trigger('change');
					$(this).trigger('change', [true]);
				});

				//Menu options
				$('.wfls-option.wfls-option-select').each(function() {
					var originalSelectValue = $(this).data('originalSelectValue');
					$(this).find('.wfls-option-select select').val(originalSelectValue).trigger('change');
					$(this).trigger('change', [true]);
				});

				//Text options
				$('.wfls-option.wfls-option-text').each(function() {
					var originalTextValue = $(this).data('originalTextValue');
					if (typeof originalTextValue !== 'undefined') {
						$(this).find('.wfls-option-text input').val(originalTextValue);
					}
					$(this).trigger('change', [true]);
				});

				//Text area options
				$('.wfls-option.wfls-option-textarea').each(function() {
					var originalTextValue = $(this).data('originalTextValue');
					$(this).find('.wfls-option-textarea textarea').val(originalTextValue);
					$(this).trigger('change', [true]);
				});

				//Token options
				$('.wfls-option.wfls-option-token').each(function() {
					var originalTokenValue = $(this).data('originalTokenValue');
					$(this).find('select').val(originalTokenValue).trigger('change');
					$(this).trigger('change', [true]);
				});

				//Switch options
				$('.wfls-option.wfls-option-switch').each(function() {
					var originalValue = $(this).data('originalValue');
					$(this).find('.wfls-switch > li').each(function() {
						$(this).toggleClass('wfls-active', originalValue == $(this).data('optionValue')).attr('aria-checked', originalValue == $(this).data('optionValue') ? 'true' : 'false');
					});
					$(this).trigger('change', [true]);
				});

				//Other options
				$(window).trigger('wflsOptionsReset');
				
				WFLS.pendingChanges = {};
				WFLS.updatePendingChanges();
			});
		},

		updatePendingChanges: function() {
			$(window).off('beforeunload', WFLS._unsavedOptionsHandler);
			if (Object.keys(WFLS.pendingChanges).length) {
				$('#wfls-cancel-changes').removeClass('wfls-disabled');
				$('#wfls-save-changes').removeClass('wfls-disabled');
				$(window).on('beforeunload', WFLS._unsavedOptionsHandler);
			}
			else {
				$('#wfls-cancel-changes').addClass('wfls-disabled');
				$('#wfls-save-changes').addClass('wfls-disabled');
			}
		},

		_unsavedOptionsHandler: function(e) {
			var message = "You have unsaved changes to your options. If you leave this page, those changes will be lost."; //Only shows on older browsers, newer browsers don't allow message customization 
			e = e || window.event;
			if (e) {
				e.returnValue = message; //IE and Firefox
			}
			return message; //Others
		},
		
		setOptions: function(options, successCallback, failureCallback) {
			if (!Object.keys(options).length) {
				return;
			}

			this.ajax('wordfence_ls_save_options', {changes: JSON.stringify(options)}, function(res) {
				if (res.success) {
					typeof successCallback == 'function' && successCallback(res);
				}
				else {
					if (res.hasOwnProperty('html') && res.html) {
						WFLS.panelModalHTML((WFLS.screenSize(500) ? '300px' : '400px'), 'Error Saving Options', res.error);
					}
					else {
						WFLS.panelModal((WFLS.screenSize(500) ? '300px' : '400px'), 'Error Saving Options', res.error);
					}

					typeof failureCallback == 'function' && failureCallback
				}
			});
		},

		saveOptions: function(successCallback, failureCallback) {
			this.setOptions(WFLS.pendingChanges, successCallback, failureCallback);
		},

		updateIPPreview: function(value, successCallback) {
			this.ajax('wordfence_ls_update_ip_preview', value, function(response) {
				if (successCallback) {
					successCallback(response);
				}
			});
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
				payload += 'action=' + action + '&nonce=' + WFLSVars.nonce;
			}
			else if (typeof(payload) == 'object' && payload instanceof Array) {
				// jQuery serialized form data
				payload.push({
					name: 'action',
					value: action
				});
				payload.push({
					name: 'nonce',
					value: WFLSVars.nonce
				});
			}
			else if (typeof(payload) == 'object') {
				payload['action'] = action;
				payload['nonce'] = WFLSVars.nonce;
			}
			
			
			$.ajax({
				type: 'POST',
				url: WFLSVars.ajaxurl,
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

		/**
		 * Displays a generic panel.
		 * 
		 * @param @param string width A width string in the format '100px'
		 * @param string heading
		 * @param string body
		 * @param object settings
		 */
		panel: function(width, heading, body, settings) {
			if (typeof settings === 'undefined') {
				settings = {};
			}
			WFLS.panelQueue.push([width, "<h3>" + heading + "</h3><p>" + body + "</p>", settings]);
			WFLS._panelServiceQueue();
		},

		/**
		 * Displays a modal panel with fixed HTML content.
		 * 
		 * @param @param string width A width string in the format '100px'
		 * @param string heading
		 * @param string body
		 * @param object settings
		 */
		panelModalHTML: function(width, heading, body, settings) {
			if (typeof settings === 'undefined') {
				settings = {};
			}

			var prompt = $.tmpl(WFLSVars.modalHTMLTemplate, {title: heading, message: body});
			var promptHTML = $("<div />").append(prompt).html();
			var callback = settings.onComplete;
			settings.overlayClose = false;
			settings.closeButton = false;
			settings.className = 'wfls-modal';
			settings.onComplete = function() {
				$('#wfls-generic-modal-close').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					WFLS.panelClose();
				});

				typeof callback === 'function' && callback();
			};
			WFLS.panelHTML(width, promptHTML, settings)
		},

		/**
		 * Displays a modal panel, automatically escaping the content.
		 *
		 * @param @param string width A width string in the format '100px'
		 * @param string heading
		 * @param string body
		 * @param object settings
		 */
		panelModal: function(width, heading, body, settings) {
			if (typeof settings === 'undefined') {
				settings = {};
			}

			var prompt = $.tmpl(WFLSVars.modalTemplate, {title: heading, message: body});

			if (typeof settings.additional_buttons !== 'undefined') {
				var buttonSection = prompt.find('.wfls-modal-footer > ul');
				for(index in settings.additional_buttons) {
					var buttonSettings = settings.additional_buttons[index];
					var button = $('<button>').text(buttonSettings.label)
						.addClass('wfls-btn wfls-btn-default wfls-btn-callout-subtle wfls-additional-button')
						.attr('id', buttonSettings.id);
					buttonSection.prepend($("<li>").addClass('wfls-padding-add-left-small').append(button));
				}
			}

			var promptHTML = $("<div />").append(prompt).html();
			var callback = settings.onComplete;
			settings.overlayClose = false;
			settings.closeButton = false;
			settings.className = 'wfls-modal';
			settings.onComplete = function() {
				$('#wfls-generic-modal-close').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					WFLS.panelClose();
				});

				typeof callback === 'function' && callback();
			};
			WFLS.panelHTML(width, promptHTML, settings)
		},

		/**
		 * Displays a modal panel with the error formatting.
		 *
		 * @param string errorMsg
		 * @param bool isTokenError Whether or not this error is an expired nonce error.
		 */
		panelError: function(errorMsg, isTokenError) {
			var callback = false;
			if (isTokenError) {
				if (WFLS.tokenErrorShowing) {
					return;
				}

				callback = function() {
					setTimeout(function() {
						WFLS.tokenErrorShowing = false;
					}, 30000);
				};

				WFLS.tokenErrorShowing = true;
			}

			var prompt = $.tmpl(WFLSVars.tokenInvalidTemplate, {title: 'An error occurred', message: errorMsg});
			var promptHTML = $("<div />").append(prompt).html();
			var settings = {};
			settings.overlayClose = false;
			settings.closeButton = false;
			settings.className = 'wfls-modal';
			settings.onComplete = function() {
				$('#wfls-token-invalid-modal-reload').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					window.location.reload(true);
				});

				typeof callback === 'function' && callback();
			};
			WFLS.panelHTML((WFLS.screenSize(500) ? '300px' : '400px'), promptHTML, settings);
		},

		/**
		 * Displays a panel with fixed HTML content.
		 *
		 * @param string width A width string in the format '100px'
		 * @param string html
		 * @param object settings
		 */
		panelHTML: function(width, html, settings) {
			if (typeof settings === 'undefined') {
				settings = {};
			}
			WFLS.panelQueue.push([width, html, settings]);
			WFLS._panelServiceQueue();
		},

		/**
		 * Displays the next panel in the queue.
		 */
		_panelServiceQueue: function() {
			if (WFLS.panelIsOpen) {
				return;
			}
			if (WFLS.panelQueue.length < 1) {
				return;
			}
			var elem = WFLS.panelQueue.shift();
			WFLS._panelOpen(elem[0], elem[1], elem[2]);
		},

		/**
		 * Does the actual function call to display the panel.
		 *
		 * @param string width A width string in the format '100px'
		 * @param string html
		 * @param object settings
		 */
		_panelOpen: function(width, html, settings) {
			this.panelIsOpen = true;
			$.extend(settings, {
				width: width,
				html: html,
				onClosed: function() {
					WFLS.panelClose();
				}
			});
			$.wflscolorbox(settings);
		},

		/**
		 * Closes the current panel.
		 */
		panelClose: function() {
			WFLS.panelIsOpen = false;
			if (WFLS.panelQueue.length < 1) {
				$.wflscolorbox.close();
			}
			else {
				WFLS._panelServiceQueue();
			}
		},

		/**
		 * Parses and returns the hash portion of a URL, working around user agents that URL-encode the # character.
		 * 
		 * @returns {Array}
		 */
		parseHashes: function() {
			var hashes = window.location.hash.replace('%23', '#');
			var splitHashes = hashes.split('#');
			var result = [];
			for (var i = 0; i < splitHashes.length; i++) {
				if (splitHashes[i].length > 0) {
					result.push(splitHashes[i]);
				}
			}
			return result;
		},

		/**
		 * Returns whether or not the screen size is within the size given. This may be a numerical value
		 * or one of the WFLS_SCREEN_ constants.
		 * 
		 * @param size
		 * @returns {boolean}
		 */
		screenSize: function(size) {
			switch (size) {
				case WFLS.SCREEN_XS:
					return window.matchMedia("only screen and (max-width: 767px)").matches;
				case WFLS.SCREEN_SM:
					return window.matchMedia("only screen and (max-width: 991px)").matches;
				case WFLS.SCREEN_MD:
					return window.matchMedia("only screen and (max-width: 1199px)").matches;
				case WFLS.SCREEN_LG:
					return window.matchMedia("only screen and (max-width: 32767px)").matches;
			}
			
			var parsed = parseInt(size);
			if (isNaN(parsed)) {
				return false;
			}
			return window.matchMedia("only screen and (max-width: " + parsed + "px)").matches;
		},
	};
	
	$(function() {
		WFLS.init();
	});

	$.fn.crossfade = function(incoming, duration, complete) {
		duration = duration || 400;
		complete = complete || function() { };
		
		return this.each(function() {
			$(this).fadeOut(duration, function() {
				$(incoming).fadeIn(duration, complete);
			});
		});
	};
})(jQuery);

/*! @source http://purl.eligrey.com/github/FileSaver.js/blob/master/FileSaver.js */
var saveAs=saveAs||function(e){"use strict";if(typeof e==="undefined"||typeof navigator!=="undefined"&&/MSIE [1-9]\./.test(navigator.userAgent)){return}var t=e.document,n=function(){return e.URL||e.webkitURL||e},r=t.createElementNS("http://www.w3.org/1999/xhtml","a"),o="download"in r,i=function(e){var t=new MouseEvent("click");e.dispatchEvent(t)},a=/constructor/i.test(e.HTMLElement),f=/CriOS\/[\d]+/.test(navigator.userAgent),u=function(t){(e.setImmediate||e.setTimeout)(function(){throw t},0)},d="application/octet-stream",s=1e3*40,c=function(e){var t=function(){if(typeof e==="string"){n().revokeObjectURL(e)}else{e.remove()}};setTimeout(t,s)},l=function(e,t,n){t=[].concat(t);var r=t.length;while(r--){var o=e["on"+t[r]];if(typeof o==="function"){try{o.call(e,n||e)}catch(i){u(i)}}}},p=function(e){if(/^\s*(?:text\/\S*|application\/xml|\S*\/\S*\+xml)\s*;.*charset\s*=\s*utf-8/i.test(e.type)){return new Blob([String.fromCharCode(65279),e],{type:e.type})}return e},v=function(t,u,s){if(!s){t=p(t)}var v=this,w=t.type,m=w===d,y,h=function(){l(v,"writestart progress write writeend".split(" "))},S=function(){if((f||m&&a)&&e.FileReader){var r=new FileReader;r.onloadend=function(){var t=f?r.result:r.result.replace(/^data:[^;]*;/,"data:attachment/file;");var n=e.open(t,"_blank");if(!n)e.location.href=t;t=undefined;v.readyState=v.DONE;h()};r.readAsDataURL(t);v.readyState=v.INIT;return}if(!y){y=n().createObjectURL(t)}if(m){e.location.href=y}else{var o=e.open(y,"_blank");if(!o){e.location.href=y}}v.readyState=v.DONE;h();c(y)};v.readyState=v.INIT;if(o){y=n().createObjectURL(t);setTimeout(function(){r.href=y;r.download=u;i(r);h();c(y);v.readyState=v.DONE});return}S()},w=v.prototype,m=function(e,t,n){return new v(e,t||e.name||"download",n)};if(typeof navigator!=="undefined"&&navigator.msSaveOrOpenBlob){return function(e,t,n){t=t||e.name||"download";if(!n){e=p(e)}return navigator.msSaveOrOpenBlob(e,t)}}w.abort=function(){};w.readyState=w.INIT=0;w.WRITING=1;w.DONE=2;w.error=w.onwritestart=w.onprogress=w.onwrite=w.onabort=w.onerror=w.onwriteend=null;return m}(typeof self!=="undefined"&&self||typeof window!=="undefined"&&window||this.content);if(typeof module!=="undefined"&&module.exports){module.exports.saveAs=saveAs}else if(typeof define!=="undefined"&&define!==null&&define.amd!==null){define([],function(){return saveAs})}

!function(t){"use strict";if(t.URL=t.URL||t.webkitURL,t.Blob&&t.URL)try{return void new Blob}catch(e){}var n=t.BlobBuilder||t.WebKitBlobBuilder||t.MozBlobBuilder||function(t){var e=function(t){return Object.prototype.toString.call(t).match(/^\[object\s(.*)\]$/)[1]},n=function(){this.data=[]},o=function(t,e,n){this.data=t,this.size=t.length,this.type=e,this.encoding=n},i=n.prototype,a=o.prototype,r=t.FileReaderSync,c=function(t){this.code=this[this.name=t]},l="NOT_FOUND_ERR SECURITY_ERR ABORT_ERR NOT_READABLE_ERR ENCODING_ERR NO_MODIFICATION_ALLOWED_ERR INVALID_STATE_ERR SYNTAX_ERR".split(" "),s=l.length,u=t.URL||t.webkitURL||t,d=u.createObjectURL,f=u.revokeObjectURL,R=u,p=t.btoa,h=t.atob,b=t.ArrayBuffer,g=t.Uint8Array,w=/^[\w-]+:\/*\[?[\w\.:-]+\]?(?::[0-9]+)?/;for(o.fake=a.fake=!0;s--;)c.prototype[l[s]]=s+1;return u.createObjectURL||(R=t.URL=function(t){var e,n=document.createElementNS("http://www.w3.org/1999/xhtml","a");return n.href=t,"origin"in n||("data:"===n.protocol.toLowerCase()?n.origin=null:(e=t.match(w),n.origin=e&&e[1])),n}),R.createObjectURL=function(t){var e,n=t.type;return null===n&&(n="application/octet-stream"),t instanceof o?(e="data:"+n,"base64"===t.encoding?e+";base64,"+t.data:"URI"===t.encoding?e+","+decodeURIComponent(t.data):p?e+";base64,"+p(t.data):e+","+encodeURIComponent(t.data)):d?d.call(u,t):void 0},R.revokeObjectURL=function(t){"data:"!==t.substring(0,5)&&f&&f.call(u,t)},i.append=function(t){var n=this.data;if(g&&(t instanceof b||t instanceof g)){for(var i="",a=new g(t),l=0,s=a.length;s>l;l++)i+=String.fromCharCode(a[l]);n.push(i)}else if("Blob"===e(t)||"File"===e(t)){if(!r)throw new c("NOT_READABLE_ERR");var u=new r;n.push(u.readAsBinaryString(t))}else t instanceof o?"base64"===t.encoding&&h?n.push(h(t.data)):"URI"===t.encoding?n.push(decodeURIComponent(t.data)):"raw"===t.encoding&&n.push(t.data):("string"!=typeof t&&(t+=""),n.push(unescape(encodeURIComponent(t))))},i.getBlob=function(t){return arguments.length||(t=null),new o(this.data.join(""),t,"raw")},i.toString=function(){return"[object BlobBuilder]"},a.slice=function(t,e,n){var i=arguments.length;return 3>i&&(n=null),new o(this.data.slice(t,i>1?e:this.data.length),n,this.encoding)},a.toString=function(){return"[object Blob]"},a.close=function(){this.size=0,delete this.data},n}(t);t.Blob=function(t,e){var o=e?e.type||"":"",i=new n;if(t)for(var a=0,r=t.length;r>a;a++)Uint8Array&&t[a]instanceof Uint8Array?i.append(t[a].buffer):i.append(t[a]);var c=i.getBlob(o);return!c.slice&&c.webkitSlice&&(c.slice=c.webkitSlice),c};var o=Object.getPrototypeOf||function(t){return t.__proto__};t.Blob.prototype=o(new t.Blob)}("undefined"!=typeof self&&self||"undefined"!=typeof window&&window||this.content||this);
