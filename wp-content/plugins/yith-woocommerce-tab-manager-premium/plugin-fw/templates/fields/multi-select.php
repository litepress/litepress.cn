<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @var array $field
 */

!defined( 'ABSPATH' ) && exit; // Exit if accessed directly

extract( $field );
if ( empty( $selects ) ){
    return;
}
?>
<div class="yith-plugin-fw-multi-select" id="<?php echo esc_attr( $id ) ?>">
    <?php
    $selects_count = count( $selects );
    for( $i = 0; $i < $selects_count; $i++ ) :
        // open group
        if( ( $i%2 ) == 0 ) : ?>
        <div class="yith-select-group">
        <?php endif; ?>

            <div class="yith-single-select">
                <?php
                $select             = $selects[$i];
                $select['type']     = 'select';
                $select['title']    = isset( $select['title'] ) ? $select['title'] : $select['name'];
                $select['name']     = $name."[{$select['id']}]";
                $select['value']    = isset( $value[$select['id']] ) ? $value[$select['id']] : $select['default'];
                $select['id']       = $name."_".$select['id'];
                $select['class']    = $class
                ?>
                <label for="<?php echo esc_attr( $select['id'] ); ?>"><?php echo esc_html( $select['title'] ); ?></label>
                <?php yith_plugin_fw_get_field( $select, true, false ); ?>
            </div>

        <?php if( ( $i%2 ) != 0 || ! isset( $selects[$i+1] ) ) : ?>
        </div>
        <?php endif;
    endfor; ?>
</div>