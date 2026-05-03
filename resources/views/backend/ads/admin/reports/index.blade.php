@extends('backend.layouts.app')

@section('title', 'Reported Ads')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h1 class="h5 mb-0">Reported Ads</h1>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="adReportsTable" class="table table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Ad</th>
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
<script src="{{ asset('assets/js/admin-ad-reports.js') }}?v={{ now()->timestamp }}"></script>
@endpush
