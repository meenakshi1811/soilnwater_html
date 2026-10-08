@extends('backend.layouts.app')

@section('title', 'Category Card Images')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <p class="ems-kicker mb-1">Homepage only</p>
            <h2 class="admin-title mb-1">Category Card Images</h2>
            <p class="mb-0 text-secondary">Set default card images per category for ads, offers, vendors, services, and consultants on the homepage. Listing and detail pages keep their own images.</p>
        </div>
        <button type="button" class="btn btn-primary js-open-category-image-modal" data-mode="create">
            <i class="fa-solid fa-plus me-1"></i> Add mapping
        </button>
    </div>

    <div class="chart-card">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary js-filter-context active" data-context="">All</button>
            @foreach($contexts as $value => $label)
                <button type="button" class="btn btn-sm btn-outline-secondary js-filter-context" data-context="{{ $value }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="table-responsive">
            <table id="categoryHomepageImagesTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Section</th>
                    <th>Updated</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryHomepageImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="categoryHomepageImageForm" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="_method" id="categoryImageFormMethod" value="POST">
                <input type="hidden" name="item_id" id="categoryImageItemId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryImageModalTitle">Add category card image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Homepage section</label>
                            <select name="context" id="categoryImageContext" class="form-select" required>
                                @foreach($contexts as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="categoryImageCategoryId" class="form-select" required>
                                <option value="">Select category…</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Card image</label>
                            <input type="file" name="image" id="categoryImageFile" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-secondary">JPG, PNG, or WebP, max 4 MB. Used on homepage cards when this category matches.</small>
                        </div>
                        <div class="col-12" id="categoryImagePreviewWrap" style="display:none;">
                            <img id="categoryImagePreview" src="" alt="" class="rounded border" style="max-height:160px;object-fit:cover;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function () {
    var routes = {
        data: @json(route('admin.category-homepage-images.data')),
        store: @json(route('admin.category-homepage-images.store')),
        show: @json(url('/admin/category-homepage-images')),
        update: @json(url('/admin/category-homepage-images')),
        destroy: @json(url('/admin/category-homepage-images')),
        categories: @json(route('admin.category-homepage-images.categories.options')),
    };

    var csrf = @json(csrf_token());
    var activeContext = '';
    var categoryOptionsLoaded = false;

    function loadCategories(selectedId) {
        return $.getJSON(routes.categories).done(function (response) {
            var $select = $('#categoryImageCategoryId');
            $select.find('option:not(:first)').remove();
            (response.categories || []).forEach(function (row) {
                $select.append($('<option>', { value: row.id, text: row.label }));
            });
            if (selectedId) {
                $select.val(String(selectedId));
            }
            categoryOptionsLoaded = true;
        });
    }

    var table = $('#categoryHomepageImagesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.data,
            data: function (d) {
                d.context = activeContext;
            }
        },
        order: [[3, 'desc']],
        columns: [
            { data: 'thumb', orderable: false, searchable: false },
            { data: 'category_label', name: 'category_label' },
            { data: 'context_label', name: 'context' },
            { data: 'updated_at', name: 'updated_at' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ]
    });

    $('.js-filter-context').on('click', function () {
        $('.js-filter-context').removeClass('active');
        $(this).addClass('active');
        activeContext = $(this).data('context') || '';
        table.ajax.reload();
    });

    var modalEl = document.getElementById('categoryHomepageImageModal');
    var modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    var $form = $('#categoryHomepageImageForm');

    function resetForm() {
        $form[0].reset();
        $('#categoryImageItemId').val('');
        $('#categoryImageFormMethod').val('POST');
        $('#categoryImagePreviewWrap').hide();
        $('#categoryImageFile').prop('required', true);
    }

    $(document).on('click', '.js-open-category-image-modal', function () {
        resetForm();
        $('#categoryImageModalTitle').text('Add category card image');
        loadCategories(null).always(function () {
            modal && modal.show();
        });
    });

    $(document).on('click', '.js-edit-category-image', function () {
        var id = $(this).data('id');
        resetForm();
        $.getJSON(routes.show + '/' + id).done(function (response) {
            var item = response.item;
            $('#categoryImageModalTitle').text('Edit category card image');
            $('#categoryImageItemId').val(item.id);
            $('#categoryImageFormMethod').val('PUT');
            $('#categoryImageContext').val(item.context);
            $('#categoryImageFile').prop('required', false);
            if (response.image_url) {
                $('#categoryImagePreview').attr('src', response.image_url);
                $('#categoryImagePreviewWrap').show();
            }
            loadCategories(item.category_id).always(function () {
                modal && modal.show();
            });
        });
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        var id = $('#categoryImageItemId').val();
        var isUpdate = !!id;
        var url = isUpdate ? routes.update + '/' + id : routes.store;
        var formData = new FormData(this);
        if (isUpdate) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrf }
        }).done(function (response) {
            toastr.success(response.message || 'Saved.');
            modal && modal.hide();
            table.ajax.reload(null, false);
        }).fail(function (xhr) {
            var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not save.';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                message = Object.values(xhr.responseJSON.errors).flat().join(' ');
            }
            toastr.error(message);
        });
    });

    $(document).on('click', '.js-delete-category-image', function () {
        var id = $(this).data('id');
        if (!window.confirm('Remove this category homepage image?')) {
            return;
        }
        $.ajax({
            url: routes.destroy + '/' + id,
            method: 'POST',
            data: { _method: 'DELETE', _token: csrf }
        }).done(function (response) {
            toastr.success(response.message || 'Removed.');
            table.ajax.reload(null, false);
        }).fail(function () {
            toastr.error('Could not delete.');
        });
    });
})();
</script>
@endpush
