@extends('backend.layouts.app')

@section('title', 'Homepage Section Order')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .section-order-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 760px;
    }

    .section-order-item,
    .section-order-fixed {
        display: flex;
        align-items: center;
        gap: 14px;
        max-width: 760px;
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }

    .section-order-item {
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        cursor: grab;
        user-select: none;
        touch-action: none;
    }

    .section-order-item:active {
        cursor: grabbing;
    }

    .section-order-item.sortable-ghost {
        opacity: 0.4;
    }

    .section-order-item.sortable-chosen {
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
    }

    .section-order-fixed {
        background: #f8fafc;
        border-style: dashed;
        color: #64748b;
    }

    .section-order-item__handle {
        color: #94a3b8;
    }

    .section-order-item__pos {
        flex-shrink: 0;
        display: grid;
        place-items: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #eef2ff;
        color: #4338ca;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .section-order-fixed .section-order-item__pos {
        background: #e2e8f0;
        color: #64748b;
    }

    .section-order-item__icon {
        width: 20px;
        color: #475569;
        text-align: center;
    }

    .section-order-item__label {
        flex: 1 1 auto;
        min-width: 0;
        font-weight: 600;
        color: #0f172a;
    }

    .section-order-item__moves {
        display: flex;
        gap: 4px;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <p class="ems-kicker mb-1">Page content</p>
            <h2 class="admin-title mb-1">Homepage Section Order</h2>
            <p class="mb-0 text-secondary">Drag the sections into the order they should appear on the homepage. Each change is saved automatically.</p>
        </div>
        <button type="button" class="btn btn-outline-secondary" id="sectionOrderReset">
            <i class="fa-solid fa-rotate-left me-1"></i> Reset to default
        </button>
    </div>

    <div class="chart-card">
        <div class="section-order-fixed mb-2">
            <span class="section-order-item__pos"><i class="fa-solid fa-thumbtack" aria-hidden="true"></i></span>
            <span class="section-order-item__icon" aria-hidden="true"><i class="fa-solid fa-store"></i></span>
            <span class="section-order-item__label text-secondary">Featured Businesses</span>
            <span class="small">Always shown first</span>
        </div>

        <ol class="section-order-list" id="sectionOrderList" aria-label="Homepage sections in display order">
            <li class="text-secondary py-3" id="sectionOrderLoading">Loading sections…</li>
        </ol>

        <p class="small text-secondary mt-3 mb-0">
            Sections marked <strong>Hidden</strong> are switched off in Page Setting. They keep their place here and appear in it once switched on.
        </p>
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
    var routes = {
        data: @json(route('admin.homepage-section-order.data')),
        update: @json(route('admin.homepage-section-order.update')),
        reset: @json(route('admin.homepage-section-order.reset')),
    };
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var list = document.getElementById('sectionOrderList');
    var resetButton = document.getElementById('sectionOrderReset');
    // Saves run one after another so a quick series of moves can never be stored out of order
    var saveQueue = Promise.resolve();

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
        }
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character];
        });
    }

    function request(url, method, body) {
        return fetch(url, {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body ? JSON.stringify(body) : undefined,
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (payload) {
                if (!response.ok) {
                    throw new Error(payload.message || 'Something went wrong. Please try again.');
                }

                return payload;
            });
        });
    }

    function render(sections) {
        list.innerHTML = sections.map(function (section) {
            var label = escapeHtml(section.label);
            var status = section.enabled
                ? '<span class="badge text-bg-success">Shown</span>'
                : '<span class="badge text-bg-secondary" title="Switched off in Page Setting">Hidden</span>';

            return '<li class="section-order-item" data-key="' + escapeHtml(section.key) + '">'
                + '<span class="section-order-item__handle" aria-hidden="true"><i class="fa-solid fa-grip-vertical"></i></span>'
                + '<span class="section-order-item__pos"></span>'
                + '<span class="section-order-item__icon" aria-hidden="true"><i class="' + escapeHtml(section.icon) + '"></i></span>'
                + '<span class="section-order-item__label">' + label + '</span>'
                + status
                + '<span class="section-order-item__moves">'
                + '<button type="button" class="btn btn-sm btn-light" data-move="-1" aria-label="Move ' + label + ' up"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></button>'
                + '<button type="button" class="btn btn-sm btn-light" data-move="1" aria-label="Move ' + label + ' down"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i></button>'
                + '</span>'
                + '</li>';
        }).join('');

        refreshPositions();
    }

    function refreshPositions() {
        var items = list.querySelectorAll('.section-order-item');
        items.forEach(function (item, index) {
            item.querySelector('.section-order-item__pos').textContent = index + 1;
            item.querySelector('[data-move="-1"]').disabled = index === 0;
            item.querySelector('[data-move="1"]').disabled = index === items.length - 1;
        });
    }

    function currentOrder() {
        return Array.prototype.map.call(list.querySelectorAll('.section-order-item'), function (item) {
            return item.dataset.key;
        });
    }

    function load() {
        return request(routes.data, 'GET').then(function (payload) {
            render(payload.sections);
        }).catch(function () {
            list.innerHTML = '<li class="text-danger py-3">Could not load the sections. Please refresh the page.</li>';
        });
    }

    function save() {
        var order = currentOrder();

        saveQueue = saveQueue.then(function () {
            return request(routes.update, 'PUT', { order: order }).then(function (payload) {
                notify('success', payload.message);
            }).catch(function (error) {
                notify('error', error.message);
                // Show what is actually saved again
                return load();
            });
        });
    }

    Sortable.create(list, {
        animation: 150,
        draggable: '.section-order-item',
        filter: 'button',
        preventOnFilter: false,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function (event) {
            if (event.oldIndex !== event.newIndex) {
                refreshPositions();
                save();
            }
        },
    });

    // Up/down buttons: the same reordering for keyboard and touch users
    list.addEventListener('click', function (event) {
        var button = event.target.closest('[data-move]');
        if (!button || button.disabled) {
            return;
        }

        var item = button.closest('.section-order-item');
        if (button.dataset.move === '-1' && item.previousElementSibling) {
            list.insertBefore(item, item.previousElementSibling);
        } else if (button.dataset.move === '1' && item.nextElementSibling) {
            list.insertBefore(item.nextElementSibling, item);
        } else {
            return;
        }

        refreshPositions();
        button.disabled ? item.querySelector('[data-move]:not(:disabled)')?.focus() : button.focus();
        save();
    });

    resetButton.addEventListener('click', function () {
        Swal.fire({
            title: 'Reset the section order?',
            text: 'The homepage sections go back to their default order.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Reset',
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            saveQueue = saveQueue.then(function () {
                return request(routes.reset, 'DELETE').then(function (payload) {
                    render(payload.sections);
                    notify('success', payload.message);
                }).catch(function (error) {
                    notify('error', error.message);
                });
            });
        });
    });

    load();
})();
</script>
@endpush
