<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

//delete_option('ywraq_toggle_element');
$defaults = array(
    'id'                => '',
    'add_button'        => '',
    'name'              => '',
    'class'             => '',
    'custom_attributes' => '',
    'elements'          => array(),
    'title'             => '',
    'subtitle'          => '',
    'onoff_field'       => array(),
    //is an array to print a onoff field, if need to call an ajax action, add  'ajax_action' => 'myaction' in the array args,
    'sortable'          => false,
    'save_button'       => array(),
    'delete_button'     => array()

);
$field    = wp_parse_args( $field, $defaults );

extract( $field );

$show_add_button   = isset( $add_button ) && $add_button;
$add_button_closed = isset( $add_button_closed ) ? $add_button_closed : '';
$values            = isset( $value ) ? $value : get_option( $name, array() );
$values            = maybe_unserialize( $values );
$sortable          = isset( $sortable ) ? $sortable : false;
$class_wrapper     = $sortable ? 'ui-sortable' : '';
$onoff_id          = isset( $onoff_field[ 'id' ] ) ? $onoff_field[ 'id' ] : '';
$ajax_nonce      = wp_create_nonce( 'save-toggle-element' );

if ( empty( $values ) && !$show_add_button && $elements ) {
    $values = array();
    //populate a toggle element with the default
    foreach ( $elements as $element ) {
        $values[ 0 ][ $element[ 'id' ] ] = $element[ 'default' ];
    }
}


?>
<div class="yith-toggle_wrapper <?php echo $class_wrapper ?>" id="<?php echo $id ?>" data-nonce="<?php echo $ajax_nonce; ?>">
    <?php

    if ( $show_add_button ):

        ?>
        <button class="yith-add-button yith-add-box-button"
                data-box_id="<?php echo $id; ?>_add_box"
                data-closed_label="<?php echo esc_attr( $add_button_closed ) ?>"
                data-opened_label="<?php echo esc_attr( $add_button ) ?>"><?php echo $add_button; ?></button>
        <div id="<?php echo $id; ?>_add_box" class="yith-add-box">
        </div>
        <script type="text/template" id="tmpl-yith-toggle-element-add-box-content-<?php echo $id ?>">
            <?php foreach ( $elements as $element ):
                $element[ 'title' ] = $element[ 'name' ];

                $element[ 'type' ] = isset( $element[ 'yith-type' ] ) ? $element[ 'yith-type' ] : $element[ 'type' ];
                unset( $element[ 'yith-type' ] );
                $element[ 'value' ] = isset( $element[ 'default' ] ) ? $element[ 'default' ] : '';
                $element[ 'id' ]    = 'new_' . $element[ 'id' ];
                $element[ 'name' ]  = $name . "[{{{data.index}}}][" . $element[ 'id' ] . "]";
                $class_element      = isset( $element[ 'class_row' ] ) ? $element[ 'class_row' ] : '';

                $is_required = !empty( $element[ 'required' ] );
                if ( $is_required ) {
                    $class_element .= ' yith-plugin-fw--required';
                }
                ?>
                <div class="yith-add-box-row <?php echo $class_element ?> <?php echo '{{{data.index}}}' ?>">

                    <label for="<?php echo $element[ 'id' ]; ?>"><?php echo( $element[ 'title' ] ); ?></label>
                    <div class="yith-plugin-fw-option-with-description">
                        <?php
                        echo yith_plugin_fw_get_field( $element, true ); ?>
                        <span class="description"><?php echo !empty( $element[ 'desc' ] ) ? $element[ 'desc' ] : ''; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if( !empty( $save_button ) ) : ?>
            <div class="yith-add-box-buttons">
                <button class="button-primary yith-save-button">
                    <?php echo $save_button[ 'name' ]; ?>
                </button>
            </div>
            <?php endif; ?>
        </script>
    <?php endif; ?>

    <div class="yith-toggle-elements">
        <?php
        if ( $values ):
            //print toggle elements
            foreach ( $values as $i => $value ):
                $title_element = yith_format_toggle_title( $title, $value );
                $title_element = apply_filters( 'yith_plugin_fw_toggle_element_title_' . $id, $title_element, $elements, $value );
                $subtitle_element = yith_format_toggle_title( $subtitle, $value );
                $subtitle_element = apply_filters( 'yith_plugin_fw_toggle_element_subtitle_' . $id, $subtitle_element, $elements, $value );
                ?>

                <div id="<?php echo $id; ?>_<?php echo $i; ?>"
                     class="yith-toggle-row <?php echo !empty( $subtitle ) ? 'with-subtitle' : ''; ?> <?php echo $class; ?>" <?php echo $custom_attributes; ?>
                     data-item_key="<?php echo esc_attr( $i ) ?>">
                    <div class="yith-toggle-title">
                        <h3>
                    <span class="title"
                          data-title_format="<?php echo esc_attr( $title ) ?>"><?php echo $title_element ?></span>
                            <?php if ( !empty( $subtitle_element ) ): ?>
                                <div class="subtitle"
                                     data-subtitle_format="<?php echo esc_attr( $subtitle ) ?>"><?php echo $subtitle_element; ?></div>
                            <?php endif; ?>
                        </h3>
                        <span class="yith-toggle">
            <span class="yith-icon yith-icon-arrow_right ui-sortable-handle"></span>
        </span>
                        <?php
                        if ( !empty( $onoff_field ) && is_array( $onoff_field ) ):
                            $action = !empty( $onoff_field[ 'ajax_action' ] ) ? 'data-ajax_action="' . $onoff_field[ 'ajax_action' ] . '"' : '';
                            $onoff_field[ 'value' ] = isset( $value[ $onoff_id ] ) ? $value[ $onoff_id ] : $onoff_field[ 'default' ];
                            $onoff_field[ 'type' ] = 'onoff';
                            $onoff_field[ 'name' ] = $name . "[$i][" . $onoff_id . "]";
                            $onoff_field[ 'id' ] = $onoff_id . '_' . $i;
                            unset( $onoff_field[ 'yith-type' ] );
                            ?>
                            <span class="yith-toggle-onoff" <?php echo $action; ?> >
                    <?php
                    echo yith_plugin_fw_get_field( $onoff_field, true );
                    ?>
                </span>

                            <?php if ( $sortable ): ?>
                            <span class="yith-icon yith-icon-drag"></span>
                        <?php endif ?>

                        <?php endif; ?>
                    </div>
                    <div class="yith-toggle-content">
                        <?php
                        if ( $elements && count( $elements ) > 0 ) {
                            foreach ( $elements as $element ):
                                $element[ 'type' ] = isset( $element[ 'yith-type' ] ) ? $element[ 'yith-type' ] : $element[ 'type' ];
                                unset( $element[ 'yith-type' ] );
                                $element[ 'title' ]     = $element[ 'name' ];
                                $element[ 'name' ]      = $name . "[$i][" . $element[ 'id' ] . "]";
                                $element[ 'value' ]     = isset( $value[ $element[ 'id' ] ] ) ? $value[ $element[ 'id' ] ] : $element[ 'default' ];
                                $element[ 'id' ]        = $element[ 'id' ] . '_' . $i;
                                $element[ 'class_row' ] = isset( $element[ 'class_row' ] ) ? $element[ 'class_row' ] : '';

                                $is_required = !empty( $element[ 'required' ] );
                                if ( $is_required ) {
                                    $element[ 'class_row' ] .= ' yith-plugin-fw--required';
                                }
                                ?>
                                <div class="yith-toggle-content-row <?php echo $element[ 'class_row' ] . ' ' . $element[ 'type' ] ?>">
                                    <label for="<?php echo $element[ 'id' ]; ?>"><?php echo $element[ 'title' ]; ?></label>
                                    <div class="yith-plugin-fw-option-with-description">
                                        <?php echo yith_plugin_fw_get_field( $element, true ); ?>
                                        <span class="description"><?php echo !empty( $element[ 'desc' ] ) ? $element[ 'desc' ] : ''; ?></span>
                                    </div>
                                </div>
                            <?php endforeach;
                        }
                        ?>
                        <div class="yith-toggle-content-buttons">
                            <div class="spinner"></div>
                            <?php
                            if ( $save_button && !empty( $save_button[ 'id' ] ) ):
                                $save_button_class = isset( $save_button[ 'class' ] ) ? $save_button[ 'class' ] : '';
                                $save_button_name = isset( $save_button[ 'name' ] ) ? $save_button[ 'name' ] : '';
                                ?>
                                <button id="<?php echo $save_button[ 'id' ]; ?>"
                                        class="yith-save-button <?php echo $save_button_class; ?>">
                                    <?php echo $save_button_name; ?>
                                </button>
                            <?php endif; ?>
                            <?php
                            if ( $delete_button && !empty( $delete_button[ 'id' ] ) ):
                                $delete_button_class = isset( $delete_button[ 'class' ] ) ? $delete_button[ 'class' ] : '';
                                $delete_button_name = isset( $delete_button[ 'name' ] ) ? $delete_button[ 'name' ] : '';
                                ?>
                                <button id="<?php echo $delete_button[ 'id' ]; ?>"
                                        class="button-secondary yith-delete-button <?php echo $delete_button_class; ?>">
                                    <?php echo $delete_button_name; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach;
        endif;
        ?>


    </div>
    <!-- Schedule Item template -->
    <script type="text/template" id="tmpl-yith-toggle-element-item-<?php echo $id ?>">
        <div id="<?php echo $id; ?>_{{{data.index}}}"
             class="yith-toggle-row  highlight <?php echo !empty( $subtitle ) ? 'with-subtitle' : ''; ?> <?php echo $class; ?>"
             data-item_key="{{{data.index}}}" <?php echo $custom_attributes; ?>
             data-item_key="{{{data.index}}}">
            <div class="yith-toggle-title">
                <h3>
                    <span class="title" data-title_format="<?php echo esc_attr( $title ) ?>"><?php echo $title ?></span>

                    <div class="subtitle"
                         data-subtitle_format="<?php echo esc_attr( $subtitle ) ?>"><?php echo $subtitle ?></div>

                </h3>
                <span class="yith-toggle">
            <span class="yith-icon yith-icon-arrow_right"></span>
        </span>
                <?php
                if ( !empty( $onoff_field ) && is_array( $onoff_field ) ):
                    $action = !empty( $onoff_field[ 'ajax_action' ] ) ? 'data-ajax_action="' . $onoff_field[ 'ajax_action' ] . '"' : '';
                    $onoff_field[ 'value' ] = $onoff_field[ 'default' ];
                    $onoff_field[ 'type' ] = 'onoff';
                    $onoff_field[ 'name' ] = $name . "[{{{data.index}}}][" . $onoff_id . "]";
                    $onoff_field[ 'id' ] = $onoff_id;
                    unset( $onoff_field[ 'yith-type' ] );
                    ?>
                    <span class="yith-toggle-onoff" <?php echo $action; ?> >
                    <?php
                    echo yith_plugin_fw_get_field( $onoff_field, true );
                    ?>
                </span>

                <?php endif; ?>
                <?php if ( $sortable ): ?>
                    <span class="yith-icon yith-icon-drag ui-sortable-handle"></span>
                <?php endif ?>
            </div>
            <div class="yith-toggle-content">
                <?php
                if ( $elements && count( $elements ) > 0 ) {
                    foreach ( $elements as $element ):
                        $element[ 'type' ] = isset( $element[ 'yith-type' ] ) ? $element[ 'yith-type' ] : $element[ 'type' ];
                        unset( $element[ 'yith-type' ] );
                        $element[ 'title' ] = $element[ 'name' ];
                        $element[ 'name' ]  = $name . "[{{{data.index}}}][" . $element[ 'id' ] . "]";
                        $element[ 'id' ]    = $element[ 'id' ] . '_{{{data.index}}}';
                        $class_element      = isset( $element[ 'class_row' ] ) ? $element[ 'class_row' ] : '';
                        $is_required = !empty( $element[ 'required' ] );
                        if ( $is_required ) {
                            $class_element .= ' yith-plugin-fw--required';
                        }
                        ?>
                        <div class="yith-toggle-content-row <?php echo $class_element . ' ' . $element[ 'type' ] ?>">
                            <label for="<?php echo $element[ 'id' ]; ?>"><?php echo $element[ 'title' ]; ?></label>
                            <div class="yith-plugin-fw-option-with-description">
                                <?php echo yith_plugin_fw_get_field( $element, true ); ?>
                                <span class="description"><?php echo !empty( $element[ 'desc' ] ) ? $element[ 'desc' ] : ''; ?></span>
                            </div>
                        </div>
                    <?php endforeach;
                }
                ?>
                <div class="yith-toggle-content-buttons">
                    <div class="spinner"></div>
                    <?php
                    if ( $save_button && !empty( $save_button[ 'id' ] ) ):
                        $save_button_class = isset( $save_button[ 'class' ] ) ? $save_button[ 'class' ] : '';
                        $save_button_name = isset( $save_button[ 'name' ] ) ? $save_button[ 'name' ] : '';
                        ?>
                        <button id="<?php echo $save_button[ 'id' ]; ?>"
                                class="yith-save-button <?php echo $save_button_class; ?>">
                            <?php echo $save_button_name; ?>
                        </button>
                    <?php endif; ?>
                    <?php
                    if ( $delete_button && !empty( $delete_button[ 'id' ] ) ):
                        $delete_button_class = isset( $delete_button[ 'class' ] ) ? $delete_button[ 'class' ] : '';
                        $delete_button_name = isset( $delete_button[ 'name' ] ) ? $delete_button[ 'name' ] : '';
                        ?>
                        <button id="<?php echo $delete_button[ 'id' ]; ?>"
                                class="button-secondary yith-delete-button <?php echo $delete_button_class; ?>">
                            <?php echo $delete_button_name; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </script>

</div>