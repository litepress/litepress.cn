<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">

	<?php $permissions['_um_bbpress_can_topic'] = get_post_meta( $object->ID, '_um_bbpress_can_topic', true );
	$permissions['_um_bbpress_can_reply'] = get_post_meta( $object->ID, '_um_bbpress_can_reply', true );

	UM()->admin_forms( array(
		'class'     => 'um-bbpress-access um-top-label',
		'prefix_id' => '',
		'fields'    => array(
			array(
				'id'        => '_um_bbpress_can_topic',
				'type'      => 'select',
				'multi'     => true,
				'label'     => __( 'Which roles can create new topics in this forum', 'um-bbpress' ),
				'value'     => ! empty( $permissions['_um_bbpress_can_topic'] ) ? $permissions['_um_bbpress_can_topic'] : array(),
				'options'   => UM()->roles()->get_roles( false, array( 'administrator' ) )
			),
			array(
				'id'        => '_um_bbpress_can_reply',
				'type'      => 'select',
				'name'      => '_um_bbpress_can_reply',
				'multi'     => true,
				'label'     => __( 'Which roles can create new replies in this forum', 'um-bbpress' ),
				'value'     => ! empty( $permissions['_um_bbpress_can_reply'] ) ? $permissions['_um_bbpress_can_reply'] : array(),
				'options'   => UM()->roles()->get_roles( false, array( 'administrator' ) )
			)
		)
	) )->render_form(); ?>

</div>