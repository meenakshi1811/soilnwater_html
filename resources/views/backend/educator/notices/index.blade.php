@extends('backend.layouts.app')
@section('title', 'Notice Board')

@section('content')
<div class="admin-panel ems-page edu-profile-page">
  <div class="mb-4">
    <p class="ems-kicker mb-1">Educator Portal</p>
    <h2 class="admin-title mb-1">Notice board</h2>
    <p class="text-secondary mb-0">Publish notices for students and parents without opening your full profile each time.</p>
  </div>

  <div class="chart-card p-3 p-lg-4">
    @include('backend.educator.partials.notice-board-manage', ['notices' => $notices ?? []])
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/educator-portal-profile.css') }}?v={{ now()->timestamp }}">
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
window.educatorNoticeStoreUrl = @json(route('educator.notices.store'));
</script>
<script src="{{ asset('assets/js/educator-notices.js') }}?v={{ now()->timestamp }}"></script>
@endpush
