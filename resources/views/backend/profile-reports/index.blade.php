@extends('backend.layouts.app')

@section('title', $title)

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h1 class="h5 mb-0">{{ $title }}</h1>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="{{ $tableId }}" class="table table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th>{{ $entityLabel }}</th>
                            <th>Reporter</th>
                            <th>Reason</th>
                            <th>Reported At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
(function ($) {
    const table = $('#{{ $tableId }}');
    if (!$ || !$.fn.DataTable || !table.length) return;

    table.DataTable({
        processing: true,
        serverSide: true,
        ajax: @json($dataUrl),
        columns: [
            { data: 'profile_name', name: 'reportable_id', orderable: false },
            { data: 'reporter_name', name: 'reporter.full_name', orderable: false },
            { data: 'reason', name: 'reason' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[3, 'desc']]
    });
})(window.jQuery);
</script>
@endpush
