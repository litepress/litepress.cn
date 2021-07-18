<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">

	<?php $role = $object['data'];

	UM()->admin_forms( array(
		'class'     => 'um-role-bbpress um-half-column',
		'prefix_id' => 'role',
		'fields'    => array(
			array(
				'id'        => '_um_can_have_forums_tab',
				'type'      => 'checkbox',
				'label'     => __( 'Can have forums tab?', 'um-bbpress' ),
				'tooltip'   => __( 'If you turn this off, this role will not have a forums tab active in their profile.', 'um-bbpress' ),
				'value'     => ! empty( $role['_um_can_have_forums_tab'] ) ? $role['_um_can_have_forums_tab'] : 0,
			),
			array(
				'id'        => '_um_can_create_topics',
				'type'      => 'checkbox',
				'label'     => __( 'Can create new topics?', 'um-bbpress' ),
				'tooltip'   => __( 'Generally, decide If this role can create new topics in the forums or not.', 'um-bbpress' ),
				'value'     => ! empty( $role['_um_can_create_topics'] ) ? $role['_um_can_create_topics'] : 0
			),
			array(
				'id'            => '_um_lock_notice2',
				'type'          => 'textarea',
				'label'         => __( 'Custom message to show if you force locking new topic', 'um-bbpress' ),
				'value'         => ! empty( $role['_um_lock_notice2'] ) ? $role['_um_lock_notice2'] : '',
				'conditional'   => array( '_um_can_create_topics', '=', '0' )
			),
			array(
				'id'        => '_um_can_create_replies',
				'type'      => 'checkbox',
				'label'     => __( 'Can create new replies?', 'um-bbpress' ),
				'tooltip'   => __( 'Generally, decide If this role can create new replies in the forums or not.', 'um-bbpress' ),
				'value'     => ! empty( $role['_um_can_create_replies'] ) ? $role['_um_can_create_replies'] : 0
			),
			array(
				'id'        => '_um_lock_days',
				'type'      => 'select',
				'multi'     => true,
				'label'     => __( 'Disable new topics during these weekdays', 'um-bbpress' ),
				'tooltip'   => __( 'Choose week days to disable this role from creating new topics on those days','um-bbpress' ),
				'value'     => ! empty( $role['_um_lock_days'] ) ? $role['_um_lock_days'] : array(),
				'options'   => UM()->bbPress_API()->get_weekdays(),
			),
			array(
				'id'    => '_um_lock_notice',
				'type'  => 'textarea',
				'label' => __( 'Custom message to show to user if user cannot post in the above selected days','um-bbpress' ),
				'value' => ! empty( $role['_um_lock_notice'] ) ? $role['_um_lock_notice'] : ''
			)
		)
	) )->render_form(); ?>

</div>