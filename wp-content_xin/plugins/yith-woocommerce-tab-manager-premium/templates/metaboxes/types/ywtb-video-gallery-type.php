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


$number_video_column_args = array(
        'id'=> $field['id'].'_video_columns',
        'name' => $field['name'].'[columns]',
        'min' => 1,
        'max' => 4,
    'type'  => 'number',
    'value' => isset( $field['value']['columns'] ) ?$field['value']['columns'] : 1,
    'class' => 'ywtb-number'
);

yith_plugin_fw_get_field( $number_video_column_args, true );

/*build table args*/
$table_id = $field['id'].'_table_video';


$values = isset( $field['value']['video_info'] ) ? $field['value']['video_info'] : array() ;

$table_columns = array(
    'video_name' => __('Video Name', 'yith-woocommerce-tab-manager'),
    'video_type' => __('Video Hosting Service', 'yith-woocommerce-tab-manager'),
    'video_id' => __('Video ID', 'yith-woocommerce-tab-manager'),
	'video_url' =>  __('Video URL', 'yith-woocommerce-tab-manager')
);

$num_columns = 2+count( $table_columns );
$field_id = $field['id'];
$i = 0;

$args = array(
        'table_columns' => $table_columns,
        'num_columns' => $num_columns,
        'add_row_label' => __( 'Add Row', 'yith-woocommerce-tab-manager' ),
        'type_row' => 'video' ,
        'values' => $values,
        'is_sortable' => true,
        'show_remove_icon' => true,
        'show_choose_file' => false,
    'field_id' => $field['id'],
    'classes' => 'ywtb-video-table'

);

wc_get_template( 'metaboxes/ywtb-table-type.php', $args, YWTM_TEMPLATE_PATH, YWTM_TEMPLATE_PATH );
?>
