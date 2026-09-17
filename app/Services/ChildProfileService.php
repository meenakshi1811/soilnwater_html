<?php

namespace App\Services;

use App\Mail\ChildProfileCreatedByParentMail;
use App\Mail\ChildProfileStatusMail;
use App\Models\ChildProfile;
use App\Models\User;
use App\Support\AuthActor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChildProfileService
{
    public function createForParent(User $parent, array $data, ?UploadedFile $profileImage = null): ChildProfile
    {
        if (! $parent->hasParentProfileEnabled()) {
            throw ValidationException::withMessages([
                'parent_profile' => 'Your parent profile must be approved before adding a child.',
            ]);
        }

        return DB::transaction(function () use ($parent, $data, $profileImage): ChildProfile {
            $childEmail = $this->generateChildEmail($parent->id);
            $childUser = User::query()->create([
                'name' => $data['full_name'],
                'full_name' => $data['full_name'],
                'email' => $childEmail,
                'phone_number' => $data['phone_number'],
                'date_of_birth' => $data['date_of_birth'],
                'role' => 'student',
                'password' => Hash::make(Str::random(32)),
                'is_active' => false,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]);

            $subjects = $this->normalizeSubjects($data['subjects'] ?? null);
            $isPrimary = (bool) ($data['is_primary'] ?? false);

            if ($isPrimary) {
                ChildProfile::query()
                    ->where('parent_user_id', $parent->id)
                    ->update(['is_primary' => false]);
            }

            $childProfile = ChildProfile::query()->create([
                'parent_user_id' => $parent->id,
                'child_user_id' => $childUser->id,
                'full_name' => $data['full_name'],
                'email' => null,
                'phone_number' => $data['phone_number'],
                'date_of_birth' => $data['date_of_birth'],
                'age' => $data['age'],
                'gender' => $data['gender'] ?? null,
                'class_grade' => $data['class_grade'] ?? null,
                'board' => $data['board'] ?? null,
                'school_name' => $data['school_name'] ?? null,
                'subjects' => $subjects,
                'is_primary' => $isPrimary,
                'status' => 'pending',
            ]);

            if ($profileImage) {
                $path = $this->storeProfileImage($profileImage, $childProfile->id);
                $childProfile->update(['profile_image' => $path]);
            }

            $parent->loadMissing('parentProfile');
            $parent->parentProfile?->recalculateCompletion();

            $parentName = $parent->full_name ?: $parent->name;

            PortalNotificationService::notifyAdminsOfApprovalRequest(
                'Child profile',
                $childProfile->full_name.' (parent: '.$parentName.')',
                route('admin.child-profiles.show', $childProfile)
            );

            $this->sendCreatedMailToParent($childProfile, $parent);

            return $childProfile->fresh(['childUser', 'parentUser']);
        });
    }

    public function approve(ChildProfile $childProfile): ChildProfile
    {
        $childProfile->loadMissing(['childUser', 'parentUser']);

        $childProfile->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => AuthActor::usersTableId(),
            'rejection_reason' => null,
        ]);

        if ($childProfile->childUser) {
            $childProfile->childUser->update(['is_active' => true]);
        }

        $this->notifyReview($childProfile, 'approved');

        return $childProfile->fresh(['childUser', 'parentUser']);
    }

    public function reject(ChildProfile $childProfile, ?string $reason = null): ChildProfile
    {
        $childProfile->loadMissing(['childUser', 'parentUser']);

        $childProfile->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => AuthActor::usersTableId(),
            'rejection_reason' => filled($reason) ? trim($reason) : 'Declined by admin.',
        ]);

        if ($childProfile->childUser) {
            $childProfile->childUser->update(['is_active' => false]);
        }

        $this->notifyReview($childProfile, 'rejected', $childProfile->rejection_reason);

        return $childProfile->fresh(['childUser', 'parentUser']);
    }

    public function delete(ChildProfile $childProfile): void
    {
        DB::transaction(function () use ($childProfile): void {
            $childUser = $childProfile->childUser;
            $parentUserId = $childProfile->parent_user_id;

            $childProfile->delete();

            if ($childUser) {
                $childUser->delete();
            }

            $parent = User::query()->find($parentUserId);
            $parent?->parentProfile?->recalculateCompletion();
        });
    }

    /**
     * @return list<string>
     */
    private function normalizeSubjects(mixed $subjects): array
    {
        if (is_string($subjects)) {
            $subjects = array_map('trim', explode(',', $subjects));
        }

        if (! is_array($subjects)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($subject) => is_string($subject) ? trim($subject) : '',
            $subjects
        )));
    }

    private function storeProfileImage(UploadedFile $file, int $childProfileId): string
    {
        $directory = public_path('uploads/child-profiles/'.$childProfileId);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/child-profiles/'.$childProfileId.'/'.$filename;
    }

    private function generateChildEmail(int $parentId): string
    {
        do {
            $email = 'child.'.$parentId.'.'.Str::lower(Str::random(12)).'@child.soilnwater.local';
        } while (User::query()->where('email', $email)->exists());

        return $email;
    }

    private function sendCreatedMailToParent(ChildProfile $childProfile, User $parent): void
    {
        if (! $parent->email) {
            return;
        }

        try {
            Mail::to($parent->email)->send(
                ChildProfileCreatedByParentMail::forParent($childProfile, $parent)
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to send child profile created email.', [
                'child_profile_id' => $childProfile->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyReview(ChildProfile $childProfile, string $action, ?string $reason = null): void
    {
        $statusLabel = $action === 'approved' ? 'approved' : 'declined';
        $parentUrl = route('parent.dashboard');

        PortalNotificationService::notifyOwnerOfReview(
            $childProfile->parentUser,
            'Child profile',
            $childProfile->full_name,
            $statusLabel,
            $parentUrl,
            $reason
        );

        try {
            if ($childProfile->parentUser?->email) {
                Mail::to($childProfile->parentUser->email)->send(
                    ChildProfileStatusMail::forParent($childProfile, $action, $reason)
                );
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to send child profile status email.', [
                'child_profile_id' => $childProfile->id,
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
