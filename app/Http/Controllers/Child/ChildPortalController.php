<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Support\AuthActor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildPortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $childProfile = $this->resolveChildProfile($request);

        return view('backend.child.dashboard', $this->buildDashboardData($childProfile, false));
    }

    public function parentView(Request $request, ChildProfile $childProfile): View
    {
        abort_unless($childProfile->parent_user_id === $request->user()->id, 403);
        abort_unless($childProfile->isApproved(), 404);

        return view('backend.child.dashboard', $this->buildDashboardData($childProfile, true));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDashboardData(ChildProfile $childProfile, bool $viewingAsParent): array
    {
        $childProfile->loadMissing(['childUser', 'parentUser']);

        $classBoard = collect([$childProfile->class_grade, $childProfile->board])->filter()->implode(' • ');
        $subjects = $childProfile->displaySubjects();
        $firstName = trim(explode(' ', trim($childProfile->full_name))[0] ?? $childProfile->full_name);

        if (empty($subjects)) {
            $subjects = ['Mathematics', 'Science', 'English', 'Social Studies', 'Hindi'];
        }

        return [
            'childProfile' => $childProfile,
            'viewingAsParent' => $viewingAsParent,
            'activeNav' => 'overview',
            'classBoard' => $classBoard,
            'firstName' => $firstName,
            'stats' => [
                'materials' => 28,
                'questions' => 12,
                'courses' => 4,
                'achievements' => 7,
            ],
            'subjects' => $subjects,
            'performance' => [
                ['label' => 'Mathematics', 'value' => 85, 'tone' => 'green'],
                ['label' => 'Science', 'value' => 78, 'tone' => 'blue'],
                ['label' => 'English', 'value' => 72, 'tone' => 'purple'],
                ['label' => 'Social Science', 'value' => 80, 'tone' => 'orange'],
                ['label' => 'Hindi', 'value' => 88, 'tone' => 'green'],
            ],
            'overallProgress' => 81,
            'recentActivity' => [
                ['icon' => 'fa-file-lines', 'tone' => 'blue', 'title' => 'Downloaded: Quadratic Equations Notes', 'meta' => 'Mathematics • 2 hours ago'],
                ['icon' => 'fa-circle-play', 'tone' => 'green', 'title' => 'Watched: Motion in a Straight Line', 'meta' => 'Science • Yesterday'],
                ['icon' => 'fa-circle-question', 'tone' => 'purple', 'title' => 'Asked: How to solve quadratic equations?', 'meta' => 'Mathematics • 2 days ago'],
                ['icon' => 'fa-book-open', 'tone' => 'orange', 'title' => 'Saved: English Grammar Notes', 'meta' => 'English • 3 days ago'],
                ['icon' => 'fa-medal', 'tone' => 'amber', 'title' => 'Earned: Science Quiz Champion', 'meta' => 'Science • 1 week ago'],
            ],
            'upcomingTests' => [
                ['day' => '18', 'month' => 'MAY', 'title' => 'Mathematics — Chapter Test 4', 'time' => '10:00 AM'],
                ['day' => '22', 'month' => 'MAY', 'title' => 'Science — Physics Unit Test', 'time' => '11:30 AM'],
                ['day' => '28', 'month' => 'MAY', 'title' => 'English — Literature Assessment', 'time' => '09:00 AM'],
            ],
            'recommended' => [
                ['type' => 'Notes', 'title' => 'Quadratic Equations — Complete Notes', 'rating' => '4.8', 'downloads' => '12.4K', 'tone' => 'blue'],
                ['type' => 'Video', 'title' => 'Light Reflection & Refraction', 'rating' => '4.6', 'downloads' => '8.2K', 'tone' => 'green'],
                ['type' => 'Paper', 'title' => 'CBSE Class 10 Maths Sample Paper', 'rating' => '4.9', 'downloads' => '15.1K', 'tone' => 'purple'],
            ],
            'teachers' => [
                ['name' => 'Dr. Amit Verma', 'subject' => 'Physics Teacher', 'rating' => '4.9'],
                ['name' => 'Priya Nair', 'subject' => 'Mathematics Teacher', 'rating' => '4.8'],
                ['name' => 'Rajesh Kumar', 'subject' => 'English Teacher', 'rating' => '4.7'],
            ],
        ];
    }

    private function resolveChildProfile(Request $request): ChildProfile
    {
        $user = AuthActor::user();
        $user->loadMissing('childProfile');

        $childProfile = $user->childProfile;

        abort_unless($childProfile && $childProfile->isApproved(), 403);

        return $childProfile;
    }
}
