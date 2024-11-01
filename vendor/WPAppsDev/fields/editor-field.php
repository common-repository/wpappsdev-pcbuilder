<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_editor( $meta, $id, $settings ) . '<br />' . $desc;
