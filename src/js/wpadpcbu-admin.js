import "../css/wpadpcbu-admin.css";
(function($) {
    'use strict';
    var frame,
        metaBox = $('.term-image-wrap'),
        addImgLink = metaBox.find('#component_media_button'),
        delImgLink = metaBox.find('#component_media_remove'),
        imgContainer = metaBox.find('#component-image-wrapper'),
        imgIdInput = metaBox.find('#component-image-id');

    addImgLink.on('click', function(event) {

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

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

            // Send the attachment URL to our custom image input field.
            imgContainer.append('<img src="' + attachment.url + '" alt="" style="max-width:100%;"/>');

            // Send the attachment id to our hidden input
            imgIdInput.val(attachment.id);

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
        imgContainer.html('');

        // Un-hide the add image link
        addImgLink.removeClass('hidden');

        // Hide the delete image link
        delImgLink.addClass('hidden');

        // Delete the image id from the hidden input
        imgIdInput.val('');

    });


    jQuery(document).ready(function($) {
        $('body').on('change', '#wpadpcbu_component', function(e) {
            e.preventDefault();
            var selectedComponent = this.value;
            //console.log(selectedComponent);

            var data = {
                action: 'generate_component_filters',
                selectedComponent: selectedComponent,
                _nonce: wpadpcbu_admin.nonce
            };

            $.ajax(wpadpcbu_admin.ajaxurl, {
                method: 'post',
                data: data,
                beforeSend: function() {
                    jQuery("#pcbuilder-settings").waitMe({ effect: 'ios' });
                },
                success: function(response) {
                    if (response.success) {
                        jQuery("#wpadpcbu_filters_div").html(response.data);
                    } else {
                        console.log(response);
                        jQuery("#wpadpcbu_filters_div").html(response.data.message);
                    }
                    jQuery("#pcbuilder-settings").waitMe('hide');
                }
            });
        });

        $('.filter-display').on('click', function(e) {
            e.preventDefault();
            var parentDiv = $(this).parent();
            $(this).text(function(i, text) {
                return text === "Show Filters" ? "Hide Filters" : "Show Filters";
            })
            parentDiv.find('.filter-list').toggleClass('wpadpcbu-hide');
        });
    });

    jQuery(document).ready(function($) {
        // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-category-ajax-response
        $(document).ajaxComplete(function(event, xhr, settings) {
            var queryStringArr = settings.data.split('&');

            if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                imgContainer.html('');
                delImgLink.addClass('hidden');
                addImgLink.removeClass('hidden');
            }
        });
    });
})(jQuery);