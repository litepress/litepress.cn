<?php
/**
 * Awesome Table Admin View
 *
 * @package    YITHEMES
 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


?>
<table class="widefat ywtb-table <?php echo $classes;?>">
    <thead>
        <tr>
            <?php if( $is_sortable ):?>
                <th class="sort"></th>
            <?php endif;?>
            <?php foreach( $table_columns as $key => $column ):?>
                <th class="<?php esc_attr_e( $key );?>"><?php echo $column;?></th>
            <?php endforeach;?>
            <?php if( $show_remove_icon ):?>
                <th></th>
            <?php endif;?>
	        <?php if( $show_choose_file ):?>
                <th></th>
	        <?php endif;?>
        </tr>
    </thead>
    <tbody>

        <?php if( is_array( $values ) && count( $values ) > 0 ) {
            $i = 0;
            foreach( $values as $value ) {
              include( 'types/views/'.$type_row.'-table-row.php');
              $i++;
             }
        }?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="<?php echo $num_columns;?>"><a href="#" class="ywtb-add-row button insert"><?php echo $add_row_label;?></a></th>
        </tr>
    </tfoot>
</table>
