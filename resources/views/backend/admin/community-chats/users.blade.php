@extends('backend.layouts.app')

@section('title', 'Chat Users')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Community Chat</p>
            <h2 class="admin-title mb-1">Chat Users</h2>
            <p class="mb-0 text-secondary">Block a user from community chat. Blocked users cannot open messenger or send messages.</p>
        </div>
        <a href="{{ route('admin.community-chats.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-comments me-1"></i> All Chats
        </a>
    </div>

    <div class="chart-card">
        <div id="chatUsersAlert" class="alert d-none" role="alert"></div>
        <div class="table-responsive">
            <table id="communityChatUsersTable" class="table table-bordered align-middle w-100"
                data-source-url="{{ route('admin.community-chats.users.data') }}">
                <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Chat status</th>
                    <th class="text-center">Block chat</th>
                    <th>Joined</th>
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
<script src="{{ asset('assets/js/admin-community-chat-users.js') }}?v={{ now()->timestamp }}"></script>
@endpush
