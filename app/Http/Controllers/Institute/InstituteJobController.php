<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Mail\InstituteJobApplicationStatusMail;
use App\Models\InstituteJob;
use App\Models\InstituteJobApplication;
use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InstituteJobController extends Controller
{
    public function index(): View
    {
        $institute = auth()->user()->institute;
        abort_unless($institute, 404);

        $jobs = $institute->jobs()
            ->withCount('applications')
            ->with(['applications' => fn ($query) => $query->with('user:id,name,email,phone_number')->latest()])
            ->latest('published_at')
            ->latest('id')
            ->paginate(15);

        return view('backend.institute.jobs.index', [
            'institute' => $institute,
            'jobs' => $jobs,
            'employmentTypes' => InstituteJob::EMPLOYMENT_TYPES,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $institute = auth()->user()->institute;
        abort_unless($institute, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'string', 'in:'.implode(',', InstituteJob::EMPLOYMENT_TYPES)],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_label' => ['nullable', 'string', 'max:255'],
            'experience_label' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:20000'],
            'requirements' => ['nullable', 'string', 'max:20000'],
            'application_deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['nullable', 'in:open,closed'],
        ]);

        $job = $institute->jobs()->create([
            ...$validated,
            'status' => $validated['status'] ?? InstituteJob::STATUS_OPEN,
            'published_at' => now(),
        ]);

        $job->loadCount('applications');

        return response()->json([
            'ok' => true,
            'message' => 'Job posted successfully.',
            'html' => view('backend.institute.partials.job-item', compact('job'))->render(),
        ]);
    }

    public function update(Request $request, InstituteJob $job): JsonResponse
    {
        $this->authorizeJob($job);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['sometimes', 'required', 'string', 'in:'.implode(',', InstituteJob::EMPLOYMENT_TYPES)],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_label' => ['nullable', 'string', 'max:255'],
            'experience_label' => ['nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:20000'],
            'requirements' => ['nullable', 'string', 'max:20000'],
            'application_deadline' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:open,closed'],
        ]);

        $job->update($validated);
        $job->loadCount('applications');
        $job->load(['applications' => fn ($query) => $query->with('user:id,name,email,phone_number')->latest()]);

        return response()->json([
            'ok' => true,
            'message' => 'Job updated successfully.',
            'html' => view('backend.institute.partials.job-item', compact('job'))->render(),
        ]);
    }

    public function destroy(InstituteJob $job): JsonResponse
    {
        $this->authorizeJob($job);
        $job->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Job removed successfully.',
        ]);
    }

    public function updateApplication(Request $request, InstituteJobApplication $application): JsonResponse
    {
        $institute = auth()->user()->institute;
        abort_unless($institute, 404);

        $application->loadMissing('job');
        abort_unless((int) $application->job?->institute_id === (int) $institute->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', InstituteJobApplication::STATUSES)],
            'institute_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $previousStatus = $application->status;

        $application->update([
            'status' => $validated['status'],
            'institute_note' => $validated['institute_note'] ?? $application->institute_note,
            'reviewed_at' => now(),
        ]);

        $application->loadMissing(['user', 'job.institute']);

        if ($previousStatus !== $application->status && $application->user) {
            $jobTitle = $application->job?->title ?? 'Job';
            $institutionName = $institute->displayName();

            PortalNotificationService::notifyUser(
                $application->user,
                'Job application update',
                $institutionName.' updated your application for '.$jobTitle.' to '.$application->statusLabel().'.',
                $institute->publicUrl(),
                'engagement'
            );

            if ($application->user->email) {
                try {
                    Mail::to($application->user->email)->send(InstituteJobApplicationStatusMail::forApplication($application));
                } catch (\Throwable $e) {
                    Log::error('Failed to send job application status mail', [
                        'application_id' => $application->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Application updated. The applicant has been notified.',
            'html' => view('backend.institute.partials.job-application-row', ['application' => $application])->render(),
        ]);
    }

    private function authorizeJob(InstituteJob $job): void
    {
        $institute = auth()->user()->institute;
        abort_unless($institute && (int) $job->institute_id === (int) $institute->id, 404);
    }
}
