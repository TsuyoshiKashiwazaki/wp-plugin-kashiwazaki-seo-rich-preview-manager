/**
 * Kashiwazaki SEO Rich Preview Manager - Metabox Scripts
 *
 * @package KashiwazakiSeoRichPreviewManager
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Media uploader instance.
     */
    let mediaUploader;

    /**
     * Initialize when DOM is ready.
     */
    $(document).ready(function() {
        initMetaboxMediaUploader();
    });

    /**
     * Initialize media uploader for metabox.
     */
    function initMetaboxMediaUploader() {
        // Select image button
        $(document).on('click', '.ksrpm-select-image', function(e) {
            e.preventDefault();

            const button = $(this);
            const imageFieldId = ksrpmMetabox.imageFieldId || 'ksrpm_og_image';
            const inputField = $('#' + imageFieldId);
            const previewSelector = ksrpmMetabox.previewSelector || '.ksrpm-image-preview';
            const previewContainer = button.closest('.ksrpm-metabox-field').find(previewSelector);

            // If the media uploader already exists, reopen it.
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Create the media uploader.
            mediaUploader = wp.media({
                title: ksrpmMetabox.selectImage || 'Select OGP Image',
                button: {
                    text: ksrpmMetabox.useThisImage || 'Use This Image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // When an image is selected, run a callback.
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();

                // Set the image URL to input field.
                inputField.val(attachment.url);

                // Update or create preview.
                const imgHtml = '<img src="' + attachment.url + '" alt="OGP Image Preview" />';

                if (previewContainer.length > 0) {
                    previewContainer.html(imgHtml);
                } else {
                    button.closest('.ksrpm-metabox-field').append(
                        '<div class="ksrpm-image-preview">' + imgHtml + '</div>'
                    );
                }
            });

            // Open the media uploader.
            mediaUploader.open();
        });

        // Remove image button
        $(document).on('click', '.ksrpm-remove-image', function(e) {
            e.preventDefault();

            const button = $(this);
            const imageFieldId = ksrpmMetabox.imageFieldId || 'ksrpm_og_image';
            const inputField = $('#' + imageFieldId);
            const previewSelector = ksrpmMetabox.previewSelector || '.ksrpm-image-preview';
            const previewContainer = button.closest('.ksrpm-metabox-field').find(previewSelector);

            // Clear input field
            inputField.val('');

            // Remove preview
            previewContainer.remove();
        });
    }

})(jQuery);
