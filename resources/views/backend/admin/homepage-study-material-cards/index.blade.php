@extends('backend.layouts.app')

@section('title', 'Study Material Cards')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .smc-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 900px;
    }

    .smc-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 10px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        cursor: grab;
        touch-action: none;
    }

    .smc-item:active {
        cursor: grabbing;
    }

    .smc-item.sortable-ghost {
        opacity: 0.4;
    }

    .smc-item.sortable-chosen {
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
    }

    .smc-item__handle {
        color: #94a3b8;
    }

    .smc-item__pos {
        flex-shrink: 0;
        display: grid;
        place-items: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f3e8ff;
        color: #6d28d9;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .smc-item__thumb {
        flex-shrink: 0;
        width: 84px;
        height: 54px;
        border-radius: 8px;
        object-fit: cover;
        background: #f1f5f9;
    }

    .smc-item__text {
        flex: 1 1 auto;
        min-width: 0;
    }

    .smc-item__title {
        margin: 0;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .smc-item__sub {
        margin: 0;
        font-size: 0.8125rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .smc-item__actions {
        display: flex;
        flex-shrink: 0;
        gap: 4px;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <p class="ems-kicker mb-1">Homepage</p>
            <h2 class="admin-title mb-1">Study Material Cards</h2>
            <p class="mb-0 text-secondary">Cards of the homepage "Study Material Library" section. Drag to set their order; every change saves automatically. Desktop shows the first 6 active cards.</p>
        </div>
        <button type="button" class="btn btn-primary" id="smcAdd">
            <i class="fa-solid fa-plus me-1"></i> Add card
        </button>
    </div>

    <div class="chart-card">
        <ol class="smc-list" id="smcList" aria-label="Study material cards in display order">
            <li class="text-secondary py-3">Loading cards…</li>
        </ol>
    </div>
</div>

<div class="modal fade" id="smcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="smcForm" enctype="multipart/form-data" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="smcModalTitle">Add card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="smcId" value="">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="smcTitle">Title</label>
                            <input type="text" name="title" id="smcTitle" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="smcSubtitle">Subtitle <span class="text-secondary fw-normal">(optional)</span></label>
                            <input type="text" name="subtitle" id="smcSubtitle" class="form-control" maxlength="255">
                            <small class="text-secondary">Shown under the title on phones.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="smcLink">Link</label>
                            <input type="text" name="link_url" id="smcLink" class="form-control" maxlength="500" required placeholder="/study-materials/notes?material_type=notes">
                            <small class="text-secondary">A page on this site (e.g. /institutes) or a full https:// address.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="smcTone">Colour</label>
                            <select name="tone" id="smcTone" class="form-select">
                                @foreach($tones as $tone)
                                    <option value="{{ $tone }}">{{ ucfirst($tone) }}</option>
                                @endforeach
                            </select>
                            <small class="text-secondary">Colour of the label on the image (phones).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="smcIcon">Icon <span class="text-secondary fw-normal">(optional)</span></label>
                            <input type="text" name="icon" id="smcIcon" class="form-control" maxlength="64" placeholder="fa-book-open">
                            <small class="text-secondary">A Font Awesome icon name, e.g. fa-award.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="smcImage">Image</label>
                            <input type="file" name="image" id="smcImage" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-secondary">Landscape photo, 16:10 (e.g. 960 × 600 px). JPG, PNG or WebP, max 4 MB.</small>
                            <img id="smcImagePreview" src="" alt="" class="img-thumbnail mt-2" style="max-height:120px" hidden>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="smcActive" value="1" checked>
                                <label class="form-check-label" for="smcActive">Active on homepage</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="smcSave">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var base = @json(url('/admin/homepage-study-material-cards'));
    var routes = {
        data: @json(route('admin.homepage-study-material-cards.data')),
        store: @json(route('admin.homepage-study-material-cards.store')),
        reorder: @json(route('admin.homepage-study-material-cards.reorder')),
    };
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var list = document.getElementById('smcList');
    var form = document.getElementById('smcForm');
    var modalEl = document.getElementById('smcModal');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var preview = document.getElementById('smcImagePreview');
    var saveQueue = Promise.resolve();

    // A failing toast library must never interrupt saving or refreshing the list
    function notify(type, message) {
        try {
            if (window.toastr && typeof window.toastr[type] === 'function') {
                window.toastr[type](message);
            }
        } catch (error) {
            // ignore
        }
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character];
        });
    }

    // JSON or multipart (FormData) request; rejects with the server's first validation message
    function request(url, method, body) {
        var isForm = body instanceof FormData;
        var headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' };
        if (body && !isForm) {
            headers['Content-Type'] = 'application/json';
        }

        return fetch(url, {
            method: method,
            headers: headers,
            body: body ? (isForm ? body : JSON.stringify(body)) : undefined,
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (payload) {
                if (!response.ok) {
                    var first = payload.errors ? Object.values(payload.errors).flat()[0] : null;
                    throw new Error(first || payload.message || 'Something went wrong. Please try again.');
                }

                return payload;
            });
        });
    }

    function render(cards) {
        if (!cards.length) {
            list.innerHTML = '<li class="text-secondary py-3">No cards yet. The homepage shows its built-in cards until you add one.</li>';
            return;
        }

        list.innerHTML = cards.map(function (card) {
            var title = escapeHtml(card.title);

            return '<li class="smc-item" data-id="' + card.id + '">'
                + '<span class="smc-item__handle" aria-hidden="true"><i class="fa-solid fa-grip-vertical"></i></span>'
                + '<span class="smc-item__pos"></span>'
                + '<img class="smc-item__thumb" src="' + escapeHtml(card.image_url) + '" alt="">'
                + '<span class="smc-item__text"><p class="smc-item__title">' + title + '</p><p class="smc-item__sub">' + escapeHtml(card.link_url) + '</p></span>'
                + (card.is_active ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Hidden</span>')
                + '<span class="smc-item__actions">'
                + '<button type="button" class="btn btn-sm btn-light" data-move="-1" aria-label="Move ' + title + ' up"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></button>'
                + '<button type="button" class="btn btn-sm btn-light" data-move="1" aria-label="Move ' + title + ' down"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i></button>'
                + '<button type="button" class="btn btn-sm btn-outline-primary" data-edit aria-label="Edit ' + title + '"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>'
                + '<button type="button" class="btn btn-sm btn-outline-danger" data-delete aria-label="Delete ' + title + '"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>'
                + '</span>'
                + '</li>';
        }).join('');

        refreshPositions();
    }

    function refreshPositions() {
        var items = list.querySelectorAll('.smc-item');
        items.forEach(function (item, index) {
            item.querySelector('.smc-item__pos').textContent = index + 1;
            item.querySelector('[data-move="-1"]').disabled = index === 0;
            item.querySelector('[data-move="1"]').disabled = index === items.length - 1;
        });
    }

    function load() {
        return request(routes.data, 'GET').then(function (payload) {
            render(payload.cards || []);
        }).catch(function () {
            list.innerHTML = '<li class="text-danger py-3">Could not load the cards. Please refresh the page.</li>';
        });
    }

    function saveOrder() {
        var order = Array.prototype.map.call(list.querySelectorAll('.smc-item'), function (item) {
            return Number(item.dataset.id);
        });

        saveQueue = saveQueue.then(function () {
            return request(routes.reorder, 'PUT', { order: order }).then(function (payload) {
                notify('success', payload.message);
            }).catch(function (error) {
                notify('error', error.message);
                return load();
            });
        });
    }

    function openForm(card) {
        form.reset();
        document.getElementById('smcId').value = card ? card.id : '';
        document.getElementById('smcModalTitle').textContent = card ? 'Edit card' : 'Add card';
        document.getElementById('smcTitle').value = card ? card.title : '';
        document.getElementById('smcSubtitle').value = card ? (card.subtitle || '') : '';
        document.getElementById('smcLink').value = card ? card.link_url : '';
        document.getElementById('smcTone').value = card ? card.tone : 'purple';
        document.getElementById('smcIcon').value = card ? (card.icon || '') : '';
        document.getElementById('smcActive').checked = card ? !!card.is_active : true;
        document.getElementById('smcImage').required = !card;
        preview.hidden = !card;
        preview.src = card ? card.image_url : '';
        modal.show();
    }

    Sortable.create(list, {
        animation: 150,
        draggable: '.smc-item',
        filter: 'button',
        preventOnFilter: false,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function (event) {
            if (event.oldIndex !== event.newIndex) {
                refreshPositions();
                saveOrder();
            }
        },
    });

    list.addEventListener('click', function (event) {
        var item = event.target.closest('.smc-item');
        if (!item) {
            return;
        }

        var move = event.target.closest('[data-move]');
        if (move && !move.disabled) {
            if (move.dataset.move === '-1' && item.previousElementSibling) {
                list.insertBefore(item, item.previousElementSibling);
            } else if (move.dataset.move === '1' && item.nextElementSibling) {
                list.insertBefore(item.nextElementSibling, item);
            }
            refreshPositions();
            move.disabled ? item.querySelector('[data-move]:not(:disabled)')?.focus() : move.focus();
            saveOrder();
            return;
        }

        if (event.target.closest('[data-edit]')) {
            request(base + '/' + item.dataset.id, 'GET').then(function (payload) {
                openForm(payload.card);
            }).catch(function (error) {
                notify('error', error.message);
            });
            return;
        }

        if (event.target.closest('[data-delete]')) {
            Swal.fire({
                title: 'Delete this card?',
                text: item.querySelector('.smc-item__title').textContent,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#dc3545',
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                request(base + '/' + item.dataset.id, 'DELETE').then(function (payload) {
                    notify('success', payload.message);
                    load();
                }).catch(function (error) {
                    notify('error', error.message);
                });
            });
        }
    });

    document.getElementById('smcAdd').addEventListener('click', function () {
        openForm(null);
    });

    document.getElementById('smcImage').addEventListener('change', function () {
        var file = this.files && this.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var id = document.getElementById('smcId').value;
        var data = new FormData(form);
        // Unchecked boxes are left out of FormData; send an explicit 0
        data.set('is_active', document.getElementById('smcActive').checked ? '1' : '0');
        if (id) {
            // Laravel reads multipart bodies only on POST, so updates use method spoofing
            data.append('_method', 'PUT');
        }

        var saveButton = document.getElementById('smcSave');
        saveButton.disabled = true;

        request(id ? base + '/' + id : routes.store, 'POST', data).then(function (payload) {
            notify('success', payload.message);
            modal.hide();
            load();
        }).catch(function (error) {
            notify('error', error.message);
        }).finally(function () {
            saveButton.disabled = false;
        });
    });

    load();
})();
</script>
@endpush
