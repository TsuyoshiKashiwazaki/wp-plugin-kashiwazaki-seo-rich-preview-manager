/**
 * Kashiwazaki SEO Rich Preview Manager - Admin Scripts
 *
 * @package KashiwazakiSeoRichPreviewManager
 * @since 2.0.0
 */

(function($) {
    'use strict';

    /**
     * Media uploader instance.
     */
    var mediaUploader;

    /**
     * Initialize when DOM is ready.
     */
    $(document).ready(function() {
        initMediaUploader();
        initPostTypeSelector();
        initDuplicateCheck();
    });

    /**
     * Initialize media uploader for settings page.
     */
    function initMediaUploader() {
        $('.ksrpm-upload-image-button').on('click', function(e) {
            e.preventDefault();

            var button = $(this);
            var inputField = $('#ksrpm_default_image');
            var previewContainer = button.closest('.ksrpm-image-upload').find('.ksrpm-image-preview');

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: ksrpmAdmin.selectImage || 'Select Image',
                button: {
                    text: ksrpmAdmin.useThisImage || 'Use This Image'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                inputField.val(attachment.url);

                if (previewContainer.length > 0) {
                    previewContainer.html('<img src="' + attachment.url + '" alt="" style="max-width: 300px; height: auto; margin-top: 10px;" />');
                } else {
                    button.closest('.ksrpm-image-upload').append(
                        '<div class="ksrpm-image-preview"><img src="' + attachment.url + '" alt="" style="max-width: 300px; height: auto; margin-top: 10px;" /></div>'
                    );
                }
            });

            mediaUploader.open();
        });

        $('.ksrpm-remove-image-button').on('click', function(e) {
            e.preventDefault();
            $('#ksrpm_default_image').val('');
            $(this).closest('.ksrpm-image-upload').find('.ksrpm-image-preview').remove();
        });
    }

    /**
     * Initialize post type selector buttons.
     */
    function initPostTypeSelector() {
        $('.ksrpm-select-all-post-types').on('click', function(e) {
            e.preventDefault();
            $('.ksrpm-post-type-checkbox').prop('checked', true);
        });

        $('.ksrpm-deselect-all-post-types').on('click', function(e) {
            e.preventDefault();
            $('.ksrpm-post-type-checkbox').prop('checked', false);
        });
    }

    /**
     * Read current toggle states from the form checkboxes.
     *
     * @return {Object} Map of feature name to boolean (checked or not).
     */
    function getToggleStates() {
        return {
            ogp:       $('input[name$="[enable_ogp]"]').is(':checked'),
            twitter:   $('input[name$="[enable_twitter_card]"]').is(':checked'),
            thumbnail: $('input[name$="[enable_meta_thumbnail]"]').is(':checked'),
            pagemap:   $('input[name$="[enable_pagemap]"]').is(':checked'),
            robots:    $('input[name$="[enable_robots_max_image]"]').is(':checked')
        };
    }

    /**
     * Strip this plugin's output blocks from HTML.
     *
     * Each feature has distinct comment markers. We remove all of them
     * so we only check for OTHER sources' output.
     *
     * @param {string} html Raw HTML from frontend.
     * @return {string} HTML with this plugin's output removed.
     */
    function stripOwnOutput(html) {
        // OGP + Robots block (wraps both OGP tags and robots meta)
        html = html.replace(/<!-- Kashiwazaki SEO Rich Preview Manager[\s\S]*?<!-- \/ Kashiwazaki SEO Rich Preview Manager -->/g, '');

        // Twitter Card block
        html = html.replace(/<!-- Twitter Card -->[\s\S]*?<!-- \/ Twitter Card -->/g, '');

        // Meta Thumbnail block
        html = html.replace(/<!-- Meta Thumbnail -->[\s\S]*?<!-- \/ Meta Thumbnail -->/g, '');

        // PageMap block (inside HTML comment)
        html = html.replace(/<!--\s*\n<PageMap>[\s\S]*?<\/PageMap>\s*\n-->/g, '');

        // Robots max-image-preview block
        html = html.replace(/<!-- Robots max-image-preview -->[\s\S]*?<!-- \/ Robots max-image-preview -->/g, '');

        return html;
    }

    /**
     * Check for other sources' output in stripped HTML.
     *
     * @param {string} html HTML with own output removed.
     * @return {Object} Map of feature name to boolean (other source detected).
     */
    function detectOtherSources(html) {
        return {
            ogp:       /<meta\s+property=["']og:/i.test(html),
            twitter:   /<meta\s+name=["']twitter:/i.test(html),
            thumbnail: /<meta\s+name=["']thumbnail["']/i.test(html),
            pagemap:   /<PageMap>/i.test(html),
            robots:    /<meta\s+name=["']robots["'][^>]*max-image-preview/i.test(html)
        };
    }

    /**
     * Build a result row for the check results table.
     *
     * Rules:
     * - No other source found         -> green  "他からの出力なし"
     * - Other found, own is OFF        -> blue   "他のプラグインまたはWP本体が出力中。ONにすると重複します。"
     * - Other found, own is ON         -> orange "現在重複しています。どちらかをOFFにしてください。"
     *
     * @param {string}  label       Feature display name.
     * @param {boolean} ownEnabled  Whether this plugin's feature is currently ON.
     * @param {boolean} otherFound  Whether another source was detected.
     * @return {string} HTML table row.
     */
    function buildResultRow(label, ownEnabled, otherFound) {
        var icon, color, message;

        if (!otherFound) {
            icon    = '&#x2714;'; // checkmark
            color   = '#00a32a';
            message = '他からの出力なし';
        } else if (!ownEnabled) {
            icon    = '&#x2139;'; // info
            color   = '#2271b1';
            message = '他のプラグインまたはWP本体が出力中。ONにすると重複します。';
        } else {
            icon    = '&#x26A0;'; // warning
            color   = '#dba617';
            message = '現在重複しています。どちらかをOFFにしてください。';
        }

        return '<tr>' +
            '<td>' + label + '</td>' +
            '<td style="color:' + color + '; font-weight: 600; text-align: center;">' + icon + '</td>' +
            '<td style="color:' + color + ';">' + message + '</td>' +
            '</tr>';
    }

    /**
     * Initialize the bulk duplicate check.
     */
    function initDuplicateCheck() {
        $(document).on('click', '.ksrpm-run-duplicate-check', function(e) {
            e.preventDefault();

            var $btn     = $(this);
            var $spinner = $('.ksrpm-check-spinner');
            var $result  = $('.ksrpm-duplicate-check-result');
            var $tbody   = $('#ksrpm-check-results-body');

            // Disable button, show spinner
            $btn.prop('disabled', true);
            $spinner.show();
            $result.hide();
            $tbody.empty();

            // Read current toggle states from form
            var toggles = getToggleStates();

            // Fetch frontend HTML with cache bust
            $.ajax({
                url: (ksrpmAdmin.homeUrl || '/') + '?ksrpm_check=1&t=' + Date.now(),
                method: 'GET',
                dataType: 'html',
                timeout: 15000,
                success: function(html) {
                    var stripped = stripOwnOutput(html);
                    var others   = detectOtherSources(stripped);

                    var rows = '';
                    rows += buildResultRow('OGP (Open Graph Protocol)', toggles.ogp,       others.ogp);
                    rows += buildResultRow('Twitter Card',              toggles.twitter,   others.twitter);
                    rows += buildResultRow('Meta Thumbnail',            toggles.thumbnail, others.thumbnail);
                    rows += buildResultRow('PageMap',                   toggles.pagemap,   others.pagemap);
                    rows += buildResultRow('Robots max-image-preview',  toggles.robots,    others.robots);

                    $tbody.html(rows);
                    $result.show();
                },
                error: function(xhr, status) {
                    var msg = 'チェックに失敗しました。';
                    if (status === 'timeout') {
                        msg += 'タイムアウトしました。';
                    }
                    msg += 'フロントページが正常に表示されるか確認してください。';

                    $tbody.html(
                        '<tr><td colspan="3" style="color: #d63638;">' + msg + '</td></tr>'
                    );
                    $result.show();
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $spinner.hide();
                }
            });
        });
    }

})(jQuery);
