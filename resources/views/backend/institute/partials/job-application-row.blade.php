<tr data-application-id="{{ $application->id }}">
    <td>
        <strong>{{ $application->user?->name ?? 'User' }}</strong>
        @if($application->user?->email)
            <div class="small text-muted">{{ $application->user->email }}</div>
        @endif
        @if($application->user?->phone_number)
            <div class="small text-muted">{{ $application->user->phone_number }}</div>
        @endif
        <div class="small text-secondary">{{ $application->created_at?->format('d M Y, H:i') }}</div>
    </td>
    <td style="white-space:pre-wrap; max-width:220px;">{{ $application->cover_message ?: '—' }}</td>
    <td>
        <span class="badge bg-light text-dark border js-inst-app-status-label">{{ $application->statusLabel() }}</span>
    </td>
    <td style="min-width: 200px;">
        <form class="js-inst-app-status-form" data-url="{{ $portalRoute('jobs.applications.update', $application) }}">
            @csrf
            @method('PATCH')
            <div class="mb-2">
                <select class="form-select form-select-sm" name="status">
                    @foreach(\App\Models\InstituteJobApplication::STATUSES as $status)
                        @php
                            $statusLabel = match ($status) {
                                'reviewed' => 'Under review',
                                'shortlisted' => 'Shortlisted',
                                'rejected' => 'Not selected',
                                'accepted' => 'Accepted',
                                default => 'Pending',
                            };
                        @endphp
                        <option value="{{ $status }}" @selected($application->status === $status)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </div>
            <textarea class="form-control form-control-sm mb-2" name="institute_note" rows="2" maxlength="5000" placeholder="Optional note to applicant">{{ $application->institute_note }}</textarea>
            <button type="submit" class="btn btn-primary btn-sm w-100 js-inst-app-submit">
                <span class="btn-text">Update &amp; notify</span>
                <span class="btn-loader d-none"><i class="fa-solid fa-spinner fa-spin"></i></span>
            </button>
        </form>
    </td>
</tr>
