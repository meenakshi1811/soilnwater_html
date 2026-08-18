@extends('backend.layouts.app')

@section('title', 'Foul Words')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community Chat</p>
            <h2 class="admin-title mb-1">Foul Words</h2>
            <p class="mb-0 text-secondary">Messages that contain these words are blocked and the user sees: “You have used the foul word.”</p>
        </div>
        <button type="button" class="btn btn-primary ems-btn-primary" id="openFoulWordModalBtn">
            <i class="fa-solid fa-plus me-2"></i> Add Word
        </button>
    </div>

    <div class="chart-card">
        <div id="foulWordAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive">
            <table id="foulWordsTable" class="table table-bordered align-middle w-100"
                data-source-url="{{ route('admin.foul-words.data') }}">
                <thead>
                <tr>
                    <th>Word</th>
                    <th>Status</th>
                    <th class="text-center">Active</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="foulWordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="foulWordModalTitle">Add Foul Word</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="foulWordForm" method="POST" action="{{ route('admin.foul-words.store') }}" novalidate>
                @csrf
                <input type="hidden" id="foulWordId" name="foul_word_id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="foulWordValue">Word</label>
                        <input type="text" name="word" id="foulWordValue" class="form-control" placeholder="Enter a word to block" maxlength="80">
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="foulWordActive" value="1" checked>
                        <label class="form-check-label" for="foulWordActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="foulWordSubmitBtn" class="btn btn-primary ems-btn-primary">
                        <span class="btn-text">Save Word</span>
                        <span class="btn-loader d-none" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/admin-foul-words.js') }}?v={{ now()->timestamp }}"></script>
@endpush
