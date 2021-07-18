/**
 * Author: Aspen Grove Studios
 * License: GNU General Public License version 3 or later
 */
function hm_bbpui_file_upload(field_id) {
	jQuery('#hm_bbpui_file_form, #hm_bbpui_file_frame').remove();
	jQuery('#' + field_id).parent().css('overflow', 'hidden')
		.append('<br /><form id="hm_bbpui_file_form" action="?hm_bbpui_do_upload=1" method="post" target="hm_bbpui_file_frame" enctype="multipart/form-data" onsubmit="hm_bbpui_file_upload_submit();" data-field-id="' + field_id + '">' +
		'<input type="file" name="hm_bbpui_file" id="hm_bbpui_file" tabindex="-1" onchange="jQuery(this).parent().submit();" />' +
		'<iframe id="hm_bbpui_file_frame" name="hm_bbpui_file_frame" />');
	jQuery('#hm_bbpui_file').trigger('click');
}

function hm_bbpui_file_upload_submit() {
	jQuery('#hm_bbpui_file_frame').on('load', function() {
		var response = jQuery('#hm_bbpui_file_frame').contents().find('body').text();
		if (response == '' || response == 'Error') {
			alert('The image could not be uploaded. Please check that it is a valid JPEG, PNG, or GIF file and that the file size is not too large.');
			response = '';
		}
		jQuery('#' + jQuery('#hm_bbpui_file_form').data('field-id')).val(response).prop('disabled', false);
		jQuery('#hm_bbpui_file_frame').off('load');
	});
	jQuery('#' + jQuery('#hm_bbpui_file_form').data('field-id')).prop('disabled', true).val('Please wait...');
	
	return true;
}