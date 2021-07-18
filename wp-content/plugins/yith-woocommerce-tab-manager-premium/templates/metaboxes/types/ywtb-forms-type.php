<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( isset( $field['label'] ) ) {
	$field['title'] = $field['label'];
}

$default_field = array(
	'id'    => '',
	'title' => isset( $field['name'] ) ? $field['name'] : '',
	'desc'  => '',
);
$field         = wp_parse_args( $field, $default_field );

extract( $field );

if( empty( $value ) ){
    $value = array();
}
$value['name']['show']    = isset ( $value['name']['show'] ) ? $value['name']['show'] : 'no';
$value['webaddr']['show'] = isset ( $value['webaddr']['show'] ) ? $value['webaddr']['show'] : 'no';
$value['subj']['show'] = isset ( $value['subj']['show'] ) ? $value['subj']['show'] : 'no';

$show_req_name   = isset ( $value['name']['req'] ) ? $value['name']['req'] : 'no';
$show_req_webaddr = isset ( $value['webaddr']['req'] ) ? $value['webaddr']['req'] : 'no';
$show_req_subj= isset ( $value['subj']['req'] ) ? $value['subj']['req'] : 'no';


$req_label_desc = __( 'Check this option to make it required.', 'yith-woocommerce-tab-manager' );
$req_label      = __( 'Required', 'yith-woocommerce-tab-manager' );
?>

<div id="ywtb-form-field-container">
    <div class="ywtb-form-row">
        <div id="ywtb-form-name" class="ywtb-field">
            <span class="description desc_name"
                  style="float: left;padding-right: 30px;"><?php _e( 'Name', 'yith-woocommerce-tab-manager' ); ?></span>
			<?php
			$field_args = array(
				'id'    => $field['id'] . '_name',
				'name'  => $field['name'] . '[name][show]',
				'value' => $value['name']['show'],
				'type'  => 'onoff'
			);

			yith_plugin_fw_get_field( $field_args, true );
			?>
        </div>
        <div id="ywtb-form-name-req" class="ywtb-field">
            <span class="description desc_name_req"
                  style="float: left;padding-right: 30px;"><?php echo $req_label; ?></span>
			<?php

			$field_args = array(
				'id'    => $field['id'] . '_name_req',
				'name'  => $field['name'] . '[name][req]',
				'value' => $show_req_name,
				'type'  => 'onoff'
			);

			yith_plugin_fw_get_field( $field_args, true );
			?>
            <span class="desc inline"><?php echo $req_label_desc; ?></span>
        </div>
    </div>
    <div class="ywtb-form-row">
        <div id="ywtb-form-webaddr" class="ywtb-field">
        <span class="description desc_webaddr"
              style="float: left;padding-right: 30px;"><?php _e( 'Website', 'yith-woocommerce-tab-manager' ); ?></span>
			<?php
			$field_args = array(
				'id'    => $field['id'] . '_website',
				'name'  => $field['name'] . '[webaddr][show]',
				'value' => $value['webaddr']['show'],
				'type'  => 'onoff'
			);

			yith_plugin_fw_get_field( $field_args, true );
			?>
        </div>
        <div id="ywtb-form-webaddr-req" class="ywtb-field">
        <span class="description desc_webaddr_req"
              style="float: left;padding-right: 30px;"><?php echo $req_label; ?></span>
			<?php

			$field_args = array(
				'id'    => $field['id'] . '_website_req',
				'name'  => $field['name'] . '[webaddr][req]',
				'value' => $show_req_webaddr,
				'type'  => 'onoff'
			);

			yith_plugin_fw_get_field( $field_args, true );
			?>
            <span class="desc inline"><?php echo $req_label_desc; ?></span>
        </div>
    </div>
    <div class="ywtb-form-row">
        <div id="ywtb-form-subject" class="ywtb-field">
        <span class="description desc_subject"
              style="float: left;padding-right: 30px;"><?php _e( 'Subject', 'yith-woocommerce-tab-manager' ); ?></span>
			<?php
			$field_args = array(
				'id'    => $field['id'] . '_subject',
				'name'  => $field['name'] . '[subj][show]',
				'value' => $value['subj']['show'],
				'type'  => 'onoff'
			);

			yith_plugin_fw_get_field( $field_args, true );
			?>
        </div>


        <div id="ywtb-form-subject-req" class="ywtb-field">
        <span class="description desc_subject_req"
              style="float: left;padding-right: 30px;"><?php echo $req_label; ?></span>
			<?php

			$field_args = array(
				'id'    => $field['id'] . '_subject_req',
				'name'  => $field['name'] . '[subj][req]',
				'value' => $show_req_subj,
				'type'  => 'onoff'
			);

			yith_plugin_fw_get_field( $field_args, true );
			?>
            <span class="desc inline"><?php echo $req_label_desc; ?></span>
        </div>
    </div>
</div>