<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use Illuminate\View\View;

class TermsAndConditionPageController extends Controller
{
    public function show(string $moduleKey): View
    {
        $terms = TermsAndCondition::query()->where('module_key', $moduleKey)->firstOrFail();

        return view('frontend.terms-and-condition', [
            'pageTitle' => $terms->module_name.' Terms & Conditions',
            'termsContent' => $terms?->content,
        ]);
    }
}
