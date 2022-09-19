<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$defaults = array(
    'id'                => '',
    'name'              => '',
    'class'             => '',
    'custom_attributes' => '',
    'elements'          => [],
    'onoff_field'       => true,

);
$field = wp_parse_args( $field, $defaults );

extract( $field );

empty( $name ) && $name = $id;
$value = get_option( $name, [] );
?>
<div class="yith-toggle_fixed_wrapper" id="<?php echo $id ?>" >
    <div class="yith-toggle-elements">
        <div id="<?php echo $id; ?>" class="yith-toggle-row fixed <?php echo ! empty( $subtitle ) ? 'with-subtitle' : ''; ?> <?php echo $class; ?>" <?php echo $custom_attributes; ?>>
                <div class="yith-toggle-title">
                    <h3>
                    <span class="title"><?php echo $title ?></span>
                    <?php if ( ! empty( $subtitle ) ): ?>
                        <span class="subtitle"><?php echo $subtitle; ?></span>
                        <?php endif; ?>
                    </h3>
                    <span class="yith-toggle"><span class="yith-icon yith-icon-arrow_right ui-sortable-handle"></span></span>
                    <?php
                    // add on off field if needed
                    if( ! empty( $onoff_field ) ) : ?>
                        <span class="yith-toggle-onoff">
                        <?php yith_plugin_fw_get_field( [
                            'type'  => 'onoff',
                            'name'  => "{$name}[enabled]",
                            'id'    => "{$id}_enabled",
                            'value' => isset( $value['enabled'] ) ? $value['enabled'] : 'no',
                        ], true );
                        ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="yith-toggle-content">
                <?php foreach ( $elements as $element ):
                        // build correct name and id
                    $field_id         = $element['id'];
                    $element['name']  = "{$name}[{$field_id}]";
                    $element['id']    = "{$id}_{$field_id}";
                    // get value
                    $element['value'] = isset( $value[ $field_id ] ) ? $value[ $field_id ] : ( isset( $element['default'] ) ? $element['default'] : '' );
                        ?>
                    <div class="yith-toggle-content-row <?php echo $element['type'] ?>">
                        <label for="<?php echo $element['id']; ?>"><?php echo $element['title']; ?></label>
                            <div class="yith-plugin-fw-option-with-description">
                            <?php yith_plugin_fw_get_field( $element, true ); ?>
                            <span class="description"><?php echo ! empty( $element['desc'] ) ? $element['desc'] : ''; ?></span>
                            </div>
                        </div>
                    <?php endforeach;
                    ?>
                </div>
            </div>
    </div>
</div>