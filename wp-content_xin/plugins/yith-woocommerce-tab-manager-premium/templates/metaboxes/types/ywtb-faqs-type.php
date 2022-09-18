<?php
if( !defined('ABSPATH')){
	exit;
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


$values = isset( $field['value'] ) ? $field['value'] : array() ;

$table_columns = array(
	'faq_name' => __('Question', 'yith-woocommerce-tab-manager'),
	'faq_desc' => __('Answer', 'yith-woocommerce-tab-manager'),
);

$num_columns = 2+count( $table_columns );
$field_id = $field['id'];
$args = array(
	'table_columns' => $table_columns,
	'num_columns' => $num_columns,
	'add_row_label' => __( 'Add Row', 'yith-woocommerce-tab-manager' ),
	'type_row' => 'faq' ,
	'values' => $values,
	'is_sortable' => true,
	'show_remove_icon' => true,
	'show_choose_file' => false,
	'field_id' => $field['id'],
	'classes' => 'ywtb-faq-table'
);

wc_get_template( 'metaboxes/ywtb-table-type.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );
?>
