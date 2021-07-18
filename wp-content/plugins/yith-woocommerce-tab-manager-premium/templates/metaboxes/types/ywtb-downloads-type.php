<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( isset( $field['label'] ) ) {
	$field['title'] = $field['label'];
}
$defaults = array(
	'id'    => '',
	'title' => isset( $field['name'] ) ? $field['name'] : '',
	'desc'  => ''
);
$field         = wp_parse_args( $field, $defaults );


/*build table args*/
$table_id = $field['id'].'_table_video';


$values = isset( $field['value'] ) ? $field['value'] : array() ;

$table_columns = array(
	'file_name' => __('File Name', 'yith-woocommerce-tab-manager'),
	'file_desc' => __( 'File Description', 'yith-woocommerce-tab-manager'),
	'file_url' =>  __('File URL', 'yith-woocommerce-tab-manager')
);

$num_columns = 3+count( $table_columns );


$args = array(
	'table_columns' => $table_columns,
	'num_columns' => $num_columns,
	'add_row_label' => __( 'Add Row', 'yith-woocommerce-tab-manager' ),
	'type_row' => 'download' ,
	'values' => $values,
	'is_sortable' => true,
	'show_remove_icon' => true,
	'show_choose_file' => true,
	'field_id' => $field['id'],
	'classes' => 'ywtb-download-table'

);

wc_get_template( 'metaboxes/ywtb-table-type.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );
?>
