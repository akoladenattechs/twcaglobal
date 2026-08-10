/**
 * Two-step admin upload flow (sermons / songs / hero / books).
 *
 * When the user picks files, they are uploaded to R2 ONE AT A TIME via AJAX
 * (the /admin/media/upload endpoint) with a visible Bootstrap progress bar
 * under the audio/image field. The returned media ids are written into hidden
 * inputs (media_ids[], track_orders[], image_id) and only THEN is the main
 * form submitted — so the save request contains just ids and is instant.
 *
 * Requires: jQuery 3.x (loaded in the admin layout) and Bootstrap 4.
 *
 * The form must carry:
 *   data-media-type="sermon" | "song" | "hero" | "book"
 *   data-upload-url="{{ route('admin.media.upload') }}"
 *
 * Existing media (edit mode) must be pre-rendered as hidden inputs:
 *   <input type="hidden" name="media_ids[]" value="...">
 *   <input type="hidden" name="track_orders[]" value="...">
 *   <input type="hidden" name="image_id" value="...">
 *   <input type="hidden" name="pdf_url" value="...">  (books)
 */
(function ($) {
    'use strict';

    if (typeof window.TwoStepUpload !== 'undefined') {
        return; // already initialised
    }

    function setup($form) {
        var mediaType = $form.data('media-type') || 'sermon';
        var uploadUrl = $form.data('upload-url');
        var $audioInput = $form.find('input[type="file"][name="audio_files[]"]').first();
        var $coverInput = $form.find('input[type="file"][name="cover_image"], input[type="file"][name="image_file"]').first();
        var $pdfInput = $form.find('input[type="file"][name="pdf_file"]').first();
        var $subfolder = $form.find('input[name="audio_subfolder"]').first();
        var $progressWrap = $form.find('.upload-progress-wrap').first();
        var $progressBar = $form.find('.upload-progress-bar').first();
        var $progressText = $form.find('.upload-progress-text').first();
        var $submitBtn = $form.find('button[type="submit"]').first();
        var $imageId = $form.find('input[name="image_id"]').first();
        var $pdfUrl = $form.find('input[name="pdf_url"]').first();
        var token = $form.find('input[name="_token"]').first().val();

        // Need at least one file input (audio, image and/or pdf) and the upload URL.
        if ((!$audioInput.length && !$coverInput.length && !$pdfInput.length) || !uploadUrl) {
            return;
        }

        // Files selected in the current modal session.
        var pendingFiles = [];

        $audioInput.on('change', function () {
            pendingFiles = Array.prototype.slice.call(this.files || []).map(function (f) {
                return { file: f, category: mediaType + '-audio' };
            });
        });

        $form.on('submit', function (e) {
            var coverFile = $coverInput.length && $coverInput[0].files && $coverInput[0].files[0] ? $coverInput[0].files[0] : null;
            var pdfFile = $pdfInput.length && $pdfInput[0].files && $pdfInput[0].files[0] ? $pdfInput[0].files[0] : null;

            // Nothing new selected -> submit normally (edit mode uses hidden ids).
            if (!pendingFiles.length && !coverFile && !pdfFile) {
                return;
            }

            e.preventDefault();

            var queue = [];
            if (coverFile) {
                queue.push({ file: coverFile, category: mediaType + '-cover' });
            }
            if (pdfFile) {
                queue.push({ file: pdfFile, category: mediaType + '-pdf' });
            }
            queue = queue.concat(pendingFiles);

            $submitBtn.prop('disabled', true);
            $progressWrap.removeClass('d-none');

            uploadNext(queue, 0);
        });

        function uploadNext(queue, index) {
            if (index >= queue.length) {
                // All files are on R2. Clear the file inputs so the direct
                // (server-side) upload path doesn't re-upload them, then
                // submit the form — it now carries only hidden ids.
                $audioInput.val('');
                if ($coverInput.length) {
                    $coverInput.val('');
                    $coverInput.next('.custom-file-label').html('Choose image...');
                }
                if ($pdfInput.length) {
                    $pdfInput.val('');
                    $pdfInput.next('.custom-file-label').html('Choose PDF...');
                }
                $audioInput.next('.custom-file-label').html('Choose audio files...');
                pendingFiles = [];
                $form[0].submit();
                return;
            }

            var item = queue[index];
            var total = queue.length;
            var label = item.category.indexOf('cover') !== -1 ? 'cover image' : item.category.indexOf('pdf') !== -1 ? 'PDF file' : 'audio file';

            $progressBar.removeClass('bg-danger').addClass('progress-bar-striped progress-bar-animated');
            $progressText.text('Uploading ' + label + ' ' + (index + 1) + ' of ' + total + ': ' + item.file.name + ' …');
            setProgress(0);

            var fd = new FormData();
            fd.append('_token', token);
            fd.append('file', item.file);
            fd.append('category', item.category);
            fd.append('subfolder', $subfolder.length ? $subfolder.val() : '');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('X-CSRF-TOKEN', token);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.onprogress = function (evt) {
                if (evt.lengthComputable) {
                    setProgress(Math.round((evt.loaded / evt.total) * 100));
                }
            };

            xhr.onload = function () {
                var res = {};
                try {
                    res = JSON.parse(xhr.responseText);
                } catch (err) { /* ignore */ }

                if (xhr.status >= 200 && xhr.status < 300 && res && res.success) {
                    if (item.category.indexOf('cover') !== -1) {
                        if (!$imageId.length) {
                            $imageId = $('<input type="hidden" name="image_id">').appendTo($form);
                        }
                        $imageId.val(res.media_id);
                    } else if (item.category.indexOf('pdf') !== -1) {
                        if (!$pdfUrl.length) {
                            $pdfUrl = $('<input type="hidden" name="pdf_url">').appendTo($form);
                        }
                        $pdfUrl.val(res.url || '');
                    } else {
                        $('<input>').attr({ type: 'hidden', name: 'media_ids[]' }).val(res.media_id).appendTo($form);
                        $('<input>').attr({ type: 'hidden', name: 'track_orders[]' }).val(res.track_order || 0).appendTo($form);
                    }
                    setProgress(100);
                    uploadNext(queue, index + 1);
                } else {
                    fail((res && res.error) || 'Server rejected the file (status ' + xhr.status + ').');
                }
            };

            xhr.onerror = function () {
                fail('Network error — check your connection and try again.');
            };

            xhr.send(fd);
        }

        function setProgress(pct) {
            $progressBar.css('width', pct + '%').attr('aria-valuenow', pct).text(pct + '%');
        }

        function fail(message) {
            $progressBar.removeClass('progress-bar-animated').addClass('bg-danger');
            $progressText.text('Upload failed: ' + message + ' No changes were saved.');
            $submitBtn.prop('disabled', false);
        }
    }

    $(function () {
        $('form[data-upload-url]').each(function () {
            setup($(this));
        });
    });

    window.TwoStepUpload = true;
})(jQuery);
