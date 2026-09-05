<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducatorListingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'city' => trim((string) $request->query('city', '')),
            'subject' => trim((string) $request->query('subject', '')),
            'takes_tuitions' => $request->query('takes_tuitions'),
        ];

        $query = Educator::query()
            ->approved()
            ->withCount(['studyMaterials as materials_count' => fn ($q) => $q->where('status', 'approved')])
            ->latest('approved_at');

        if ($filters['q'] !== '') {
            $query->where(function ($q) use ($filters): void {
                $q->where('display_name', 'like', '%'.$filters['q'].'%')
                    ->orWhere('professional_headline', 'like', '%'.$filters['q'].'%')
                    ->orWhere('associated_institute', 'like', '%'.$filters['q'].'%');
            });
        }

        if ($filters['city'] !== '') {
            $query->where('city', 'like', '%'.$filters['city'].'%');
        }

        if ($filters['subject'] !== '') {
            $query->where('subjects', 'like', '%'.$filters['subject'].'%');
        }

        if ($filters['takes_tuitions'] === '1') {
            $query->where('take_tuitions', true);
        } elseif ($filters['takes_tuitions'] === '0') {
            $query->where('take_tuitions', false);
        }

        $educators = $query->paginate(12)->withQueryString();

        return view('frontend.educator.index', compact('educators', 'filters'));
    }
}
