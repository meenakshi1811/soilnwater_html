@extends('backend.layouts.app')

@section('title', 'Homepage Showcase')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    /* The form wraps the modal header/body/footer, so it has to be the shrinking flex column
       for .modal-dialog-scrollable to scroll the body instead of clipping it */
    #homepageShowcaseForm {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <p class="ems-kicker mb-1">Page content</p>
            <h2 class="admin-title mb-1">Homepage Showcase</h2>
            <p class="mb-0 text-secondary">Manage Featured Businesses and Latest Offers cards on the homepage.</p>
        </div>
        <button type="button" class="btn btn-primary js-open-showcase-modal" data-mode="create">
            <i class="fa-solid fa-plus me-1"></i> Add item
        </button>
    </div>

    <div class="chart-card">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary js-filter-type active" data-type="">All</button>
            @foreach($types as $value => $label)
                <button type="button" class="btn btn-sm btn-outline-secondary js-filter-type" data-type="{{ $value }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="table-responsive">
            <table id="homepageShowcaseTable" class="table table-bordered align-middle w-100">
                <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="homepageShowcaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="homepageShowcaseForm" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="_method" id="showcaseFormMethod" value="POST">
                <input type="hidden" name="item_id" id="showcaseItemId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="showcaseModalTitle">Add homepage item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select name="type" id="showcaseType" class="form-select" required>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort order</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="0">
                        </div>
                        {{-- Only the fields the homepage uses: shared ones, then the offer-only ones --}}
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                            <small class="text-muted js-field-featured">Business name, used as the image's alternative text.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link URL</label>
                            <input type="url" name="link_url" class="form-control" required placeholder="https://">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Colour</label>
                            <select name="category_tone" class="form-select">
                                @foreach($tones as $tone)
                                    <option value="{{ $tone }}">{{ ucfirst($tone) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted js-field-featured">Border colour around the card.</small>
                            <small class="text-muted js-field-offer">Colour of the category tag.</small>
                        </div>
                        <div class="col-12 js-field-offer">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Short offer description"></textarea>
                        </div>
                        <div class="col-md-6 js-field-offer">
                            <label class="form-label">Category label</label>
                            <input type="text" name="category_label" class="form-control" placeholder="Restaurant">
                        </div>
                        <div class="col-md-6 js-field-offer">
                            <label class="form-label">Category icon</label>
                            <input type="text" name="category_icon" class="form-control" placeholder="fa-store">
                        </div>
                        <div class="col-md-6 js-field-offer">
                            <label class="form-label">Discount badge</label>
                            <input type="text" name="discount_badge" class="form-control" placeholder="30% OFF">
                        </div>
                        <div class="col-md-6 js-field-offer">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control">
                        </div>
                        <div class="col-md-6 js-field-offer">
                            <label class="form-label">Valid until</label>
                            <input type="date" name="valid_until" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" id="showcaseImage" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted js-field-featured">Portrait design, 3:4 (e.g. 900 × 1200 px). Any message goes in the image itself.</small>
                            <small class="text-muted js-field-offer">Landscape photo, 16:10 (e.g. 960 × 600 px).</small>
                            <img id="showcaseImagePreview" src="" alt="" class="img-thumbnail mt-2 d-none" style="max-height:120px">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="showcaseIsActive" value="1" checked>
                                <label class="form-check-label" for="showcaseIsActive">Active on homepage</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="showcaseSubmitBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function ($) {
    var TYPE_FEATURED = @json(\App\Models\HomepageShowcaseItem::TYPE_FEATURED_BUSINESS);
    var TYPE_OFFER = @json(\App\Models\HomepageShowcaseItem::TYPE_OFFER_PROMO);
    var routes = {
        data: @json(route('admin.homepage-showcase.data')),
        store: @json(route('admin.homepage-showcase.store')),
        show: @json(url('/admin/homepage-showcase')),
        update: @json(url('/admin/homepage-showcase')),
        destroy: @json(url('/admin/homepage-showcase')),
    };

    function toast(type, message) {
        if (window.toastr) toastr[type === 'success' ? 'success' : 'error'](message);
        else alert(message);
    }

    function toggleTypeFields(type) {
        var isFeatured = type === TYPE_FEATURED;
        $('.js-field-featured').toggle(isFeatured);
        $('.js-field-offer').toggle(!isFeatured);
    }

    var typeFilter = '';
    var table = $('#homepageShowcaseTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.data,
            data: function (d) { d.type = typeFilter; }
        },
        columns: [
            { data: 'thumb', orderable: false, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'type_label', name: 'type' },
            { data: 'category_label', name: 'category_label' },
            { data: 'location', name: 'location' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[5, 'asc']]
    });

    $('.js-filter-type').on('click', function () {
        typeFilter = $(this).data('type') || '';
        $('.js-filter-type').removeClass('active');
        $(this).addClass('active');
        table.ajax.reload();
    });

    var modalEl = document.getElementById('homepageShowcaseModal');
    var modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    var $form = $('#homepageShowcaseForm');

    function resetForm() {
        $form[0].reset();
        $('#showcaseItemId').val('');
        $('#showcaseFormMethod').val('POST');
        $('#showcaseModalTitle').text('Add homepage item');
        $('#showcaseImage').prop('required', true);
        $('#showcaseIsActive').prop('checked', true);
        $('#showcaseImagePreview').addClass('d-none').attr('src', '');
        toggleTypeFields($('#showcaseType').val());
    }

    $('.js-open-showcase-modal').on('click', function () {
        resetForm();
        modal.show();
    });

    $('#showcaseType').on('change', function () {
        toggleTypeFields($(this).val());
    });

    $(document).on('click', '.js-edit-showcase-item', function () {
        var id = $(this).data('id');
        resetForm();
        $.get(routes.show + '/' + id)
            .done(function (res) {
                var item = res.item;
                $('#showcaseItemId').val(item.id);
                $('#showcaseFormMethod').val('PUT');
                $('#showcaseModalTitle').text('Edit homepage item');
                $('#showcaseImage').prop('required', false);
                $('#showcaseType').val(item.type);
                toggleTypeFields(item.type);
                $form.find('[name="title"]').val(item.title);
                $form.find('[name="description"]').val(item.description || '');
                $form.find('[name="link_url"]').val(item.link_url);
                $form.find('[name="location"]').val(item.location || '');
                $form.find('[name="category_label"]').val(item.category_label || '');
                $form.find('[name="category_tone"]').val(item.category_tone || 'slate');
                $form.find('[name="category_icon"]').val(item.category_icon || 'fa-store');
                $form.find('[name="discount_badge"]').val(item.discount_badge || '');
                $form.find('[name="valid_until"]').val(item.valid_until ? item.valid_until.substring(0, 10) : '');
                $form.find('[name="sort_order"]').val(item.sort_order ?? 0);
                $('#showcaseIsActive').prop('checked', !!item.is_active);
                if (res.image_url) {
                    $('#showcaseImagePreview').attr('src', res.image_url).removeClass('d-none');
                }
                modal.show();
            })
            .fail(function () { toast('error', 'Unable to load item.'); });
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        var id = $('#showcaseItemId').val();
        var isUpdate = !!id;
        var url = isUpdate ? routes.update + '/' + id : routes.store;
        var formData = new FormData(this);
        if (!formData.has('is_active')) {
            formData.append('is_active', '0');
        }
        $('#showcaseSubmitBtn').prop('disabled', true);
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
        }).done(function (res) {
            toast('success', res.message || 'Saved.');
            modal.hide();
            table.ajax.reload(null, false);
        }).fail(function (xhr) {
            var msg = xhr.responseJSON?.message;
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat()[0];
            }
            toast('error', msg || 'Unable to save item.');
        }).always(function () {
            $('#showcaseSubmitBtn').prop('disabled', false);
        });
    });

    $(document).on('click', '.js-delete-showcase-item', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Delete this item?',
            text: 'It will be removed from the homepage showcase.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc3545',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: routes.destroy + '/' + id,
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
            }).done(function (res) {
                toast('success', res.message || 'Deleted.');
                table.ajax.reload(null, false);
            }).fail(function () {
                toast('error', 'Unable to delete item.');
            });
        });
    });

    toggleTypeFields($('#showcaseType').val());
})(jQuery);
</script>
@endpush
