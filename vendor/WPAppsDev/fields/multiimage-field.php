<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<div class="gallery-section">
		<div class="gallery-screenshot clearfix tj-row">
			<?php
			if ( strlen($meta) ) {
				$ids = explode( ',', $meta );

				foreach ( $ids as $attachment_id ) {
					$img = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
					echo '<div class="screen-thumb tj-col-3"><img src="' . esc_url( $img[0] ) . '" /></div>';
				}
			}
			?>
		</div>

		<!-- <input id="edit-gallery" class="button upload_gallery_button" type="button" value="<?php esc_html_e( 'Add/Edit Gallery' ); ?>"/>
		<input id="clear-gallery" class="button upload_gallery_button" type="button" value="<?php esc_html_e( 'Clear' ); ?>"/> -->
		<a id="edit-gallery" class="button upload_gallery_button" type="button" value="<?php esc_html_e( 'Add/Edit Gallery' ); ?>"><?php esc_html_e( 'Add/Edit Gallery' ); ?></a>
		<a id="clear-gallery" class="button upload_gallery_button" type="button" value="<?php esc_html_e( 'Clear' ); ?>"><?php esc_html_e( 'Clear' ); ?></a>
		<input type="txt" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="gallery_values" value="<?php echo esc_attr( $meta ); ?>" style="display: none;">
	</div>
