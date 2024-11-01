<?php
// File Security Check
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $image = str_replace( '/fields', '', plugin_dir_url( __FILE__ ) ) . '/assets/images/image.png';
// echo '<div class="meta_box_image"><span class="meta_box_default_image" style="display:none">' . $image . '</span>';
// if ( $meta ) {
// 	$image = wp_get_attachment_image_src( intval( $meta ), 'medium' );
// 	$image = $image[0];
// }
// echo	'<input name="' . esc_attr( $name ) . '" type="hidden" class="meta_box_upload_image" value="' . intval( $meta ) . '" />
// 			<img src="' . esc_attr( $image ) . '" class="meta_box_preview_image" alt="" />
// 				<a href="#" class="meta_box_upload_image_button button" rel="' . get_the_ID() . '">Choose Image</a>
// 				<small>&nbsp;<a href="#" class="meta_box_clear_image_button">Remove Image</a></small></div>
// 				<br clear="all" />' . $desc;

// wpadcp_print($args);
$value = $this->get_option( $args['id'], $args['section'], $args['std'] );

if ( empty( $value ) ) {
	$remove_media = 'hidden';
	$add_media    = '';
} else {
	$add_media    = 'hidden';
	$remove_media = '';
}

$key              = "{$args['section']}_{$args['id']}";
$add_media_key    = "{$key}_add_media";
$remove_media_key = "{$key}_remove_media";
$input_wrap       = "{$key}_image_wrap";
?>
<div class="form-field wpappsdev_image_wrap" id="<?php echo $input_wrap; ?>" >
	<?php echo sprintf( '<input type="hidden" class="wpappsdev_media_id" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s"/>', $args['section'], $args['id'], $value ); ?>
	<div id="wpappsdev_display_image" class="wpappsdev_display_image" style="width: 100%;">
		<?php if ( $value ) { ?>
			<?php echo wp_kses( wp_get_attachment_image( $value, 'full' ), $this->wpadpcbu_allowed_html() ); ?>
		<?php } ?>
	</div>
	<p>
		<a id="<?php echo $add_media_key; ?>" class="button button-secondary <?php echo $add_media; ?>">Add Image</a>
		<a id="<?php echo $remove_media_key; ?>" class="button button-secondary <?php echo $remove_media; ?>">Remove Image</a>
	</p>
</div>

<script>
(function($) {
	'use strict';

	jQuery(document).ready(function($) {
		var frame;
		var addImgLink = $('#<?php echo $add_media_key; ?>');
		var delImgLink = $('#<?php echo $remove_media_key; ?>');
		var imageWrap = $('#<?php echo $input_wrap; ?>');

		addImgLink.on('click', function(event) {
			event.preventDefault();
			// If the media frame already exists, reopen it.
			if (frame) {
				frame.open();
				return;
			}

			var displayImage = imageWrap.find('.wpappsdev_display_image');
			var mediaId = imageWrap.find('.wpappsdev_media_id');

			// Create a new media frame
			frame = wp.media({
				title: 'Select or Upload Media',
				button: {
					text: 'Use this media'
				},
				multiple: false // Set to true to allow multiple files to be selected
			});
			// When an image is selected in the media frame...
			frame.on('select', function() {
				// Get media attachment details from the frame state
				var attachment = frame.state().get('selection').first().toJSON();
				console.debug(imageWrap);
				// Send the attachment URL to our custom image input field.
				displayImage.html('<img src="' + attachment.url + '" alt=""/>');
				// Send the attachment id to our hidden input
				mediaId.val(attachment.id);
				// Hide the add image link
				addImgLink.addClass('hidden');
				// Unhide the remove image link
				delImgLink.removeClass('hidden');
			});
			// Finally, open the modal on click
			frame.open();
		});

		delImgLink.on('click', function(event) {
			event.preventDefault();
			// Clear out the preview image
			imageWrap.find('.wpappsdev_display_image').html('');
			// Delete the image id from the hidden input
			imageWrap.find('.wpappsdev_media_id').val('');
			// Hide the add image link
			addImgLink.removeClass('hidden');
			// Unhide the remove image link
			delImgLink.addClass('hidden');
		});
	});
})(jQuery);
</script>
