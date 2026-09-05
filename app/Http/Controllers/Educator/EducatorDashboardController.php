<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class EducatorDashboardController extends Controller
{
    public function dashboard(): View
    {
        $educator = auth()->user()->educator;
        $educator->loadCount([
            'studyMaterials',
            'studyMaterials as approved_materials_count' => fn ($q) => $q->where('status', 'approved'),
            'studyMaterials as pending_materials_count' => fn ($q) => $q->where('status', 'pending'),
            'enquiries',
            'reviews',
            'followers',
        ]);

        $completeness = $this->profileCompleteness($educator);

        return view('backend.educator.dashboard', [
            'educator' => $educator,
            'completeness' => $completeness,
            'stats' => [
                [
                    'label' => 'Study materials',
                    'value' => $educator->study_materials_count,
                    'detail' => sprintf(
                        '%s approved · %s pending',
                        number_format($educator->approved_materials_count),
                        number_format($educator->pending_materials_count)
                    ),
                    'url' => route('educator.materials.index'),
                    'icon' => 'fa-book-open',
                    'class' => 'stat-purple',
                ],
                [
                    'label' => 'Enquiries',
                    'value' => $educator->enquiries_count,
                    'detail' => 'Messages from students and parents',
                    'url' => route('educator.enquiries.index'),
                    'icon' => 'fa-envelope-open-text',
                    'class' => 'stat-blue',
                ],
                [
                    'label' => 'Reviews',
                    'value' => $educator->reviews_count,
                    'detail' => number_format((float) $educator->average_rating, 1).' average rating',
                    'url' => $educator->publicUrl(),
                    'icon' => 'fa-star',
                    'class' => 'stat-cyan',
                ],
                [
                    'label' => 'Profile completeness',
                    'value' => $completeness.'%',
                    'detail' => 'Keep your public profile up to date',
                    'url' => route('educator.profile.edit'),
                    'icon' => 'fa-user-check',
                    'class' => 'stat-orange',
                ],
            ],
        ]);
    }

    private function profileCompleteness($educator): int
    {
        $fields = [
            $educator->display_name,
            $educator->profile_photo,
            $educator->professional_headline,
            $educator->tagline,
            $educator->associated_institute,
            $educator->city,
            $educator->pincode,
            $educator->residential_address,
            $educator->about,
            $educator->subjects,
            $educator->classes,
            $educator->qualifications,
            $educator->experiences,
            $educator->teaching_modes,
            $educator->languages,
            $educator->availability,
            $educator->years_experience > 0,
            $educator->phone,
            $educator->email,
        ];

        $filled = collect($fields)->filter(function ($value) {
            if (is_bool($value)) {
                return $value;
            }
            if (is_array($value)) {
                return count($value) > 0;
            }

            return filled($value);
        })->count();

        return (int) round(($filled / max(count($fields), 1)) * 100);
    }
}
