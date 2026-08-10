{{--
    Reusable two-step upload progress indicator.

    Used inside any admin form that carries:
      data-upload-url="{{ route('admin.media.upload') }}"
      data-media-type="sermon" | "song" | "hero"

    Class-based selectors (no IDs) so multiple instances can live on one
    page — e.g. the add & edit modals on the hero page. `admin-upload.js`
    finds them within the form scope via `.upload-progress-wrap`,
    `.upload-progress-bar` and `.upload-progress-text`.
--}}
<div class="upload-progress-wrap mt-3 d-none">
    <div class="progress" style="height: 20px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated upload-progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
    </div>
    <small class="form-text text-muted mt-1 upload-progress-text">Uploading...</small>
</div>
