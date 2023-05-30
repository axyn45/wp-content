<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-admin-metabox">

	<?php $role = $object['data'];

	UM()->admin_forms( [
		'class'     => 'um-role-notes um-half-column',
		'prefix_id' => 'role',
		'fields'    => [
			[
				'id'        => '_um_disable_notes',
				'type'      => 'checkbox',
				'label'     => __( 'Disable notes feature?', 'um-user-notes' ),
				'tooltip'   => __( 'Can this role have notes feature?', 'um-user-notes' ),
				'value'     => isset( $role['_um_disable_notes'] ) ? $role['_um_disable_notes'] : 0,
			],
		],
	] )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>