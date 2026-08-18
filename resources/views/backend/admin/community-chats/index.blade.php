@extends('backend.layouts.app')

@section('title', 'Community Chats')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community Chat</p>
            <h2 class="admin-title mb-1">All Chats</h2>
            <p class="mb-0 text-secondary">Review every public chat, group, and group topic. Open a conversation to read messages and block users.</p>
        </div>
        <a href="{{ route('admin.community-chats.users') }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-user-slash me-1"></i> Chat Users
        </a>
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table id="communityChatsTable" class="table table-bordered align-middle w-100"
                data-source-url="{{ route('admin.community-chats.data') }}">
                <thead>
                <tr>
                    <th>Chat</th>
                    <th>Type</th>
                    <th>Started by</th>
                    <th>Messages</th>
                    <th>Last activity</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/js/admin-community-chats.js') }}?v={{ now()->timestamp }}"></script>
@endpush
