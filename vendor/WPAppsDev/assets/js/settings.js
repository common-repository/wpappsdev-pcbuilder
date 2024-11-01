// (function($) {
//     'use strict';

//     jQuery(document).ready(function($) {
//         var frame;
//         var addImgLink = $('.wpappsdev_add_media');
//         var delImgLink = $('.wpappsdev_remove_media');

//         addImgLink.on('click', function(event) {
//             event.preventDefault();
//             // If the media frame already exists, reopen it.
//             if (frame) {
//                 frame.open();
//                 return;
//             }
//             var tdWrap = $(this).closest('td');
//             var imageWrap = tdWrap.find('td div.wpappsdev_image_wrap');
//             var displayImage = imageWrap.find('#wpappsdev_display_image');
//             var mediaId = imageWrap.find('.wpappsdev_media_id');
//             var addMedia = imageWrap.find('.wpappsdev_add_media');
//             var removeMedia = imageWrap.find('.wpappsdev_remove_media');

//             // Create a new media frame
//             frame = wp.media({
//                 title: 'Select or Upload Media',
//                 button: {
//                     text: 'Use this media'
//                 },
//                 multiple: false // Set to true to allow multiple files to be selected
//             });
//             // When an image is selected in the media frame...
//             frame.on('select', function() {
//                 // Get media attachment details from the frame state
//                 var attachment = frame.state().get('selection').first().toJSON();
//                 console.debug(imageWrap);
//                 // Send the attachment URL to our custom image input field.
//                 displayImage.html('<img width="150" height="150" src="' + attachment.url + '" alt=""/>');
//                 // Send the attachment id to our hidden input
//                 mediaId.val(attachment.id);
//                 // Hide the add image link
//                 addMedia.addClass('hidden');
//                 // Unhide the remove image link
//                 removeMedia.removeClass('hidden');
//             });
//             // Finally, open the modal on click
//             frame.open();
//         });

//         delImgLink.on('click', function(event) {
//             event.preventDefault();
//             var imageWrap = $(this).closest('.wpappsdev_image_wrap');
//             // Clear out the preview image
//             imageWrap.find('.wpappsdev_display_image').html('');
//             // Delete the image id from the hidden input
//             imageWrap.find('.wpappsdev_media_id').val('');
//             // Hide the add image link
//             imageWrap.find('.wpappsdev_add_media').removeClass('hidden');
//             // Unhide the remove image link
//             imageWrap.find('.wpappsdev_remove_media').addClass('hidden');
//         });
//     });
// })(jQuery);