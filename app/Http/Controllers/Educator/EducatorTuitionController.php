<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Support\EducatorTuitionUpdater;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducatorTuitionController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('educator');
        $educator = $user->educator;
        abort_unless($educator, 403);

        return view('backend.educator.tuition', [
            'user' => $user,
            'educator' => $educator,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $educator = $request->user()->educator;
        abort_unless($educator, 403);

        $validated = $request->validate(EducatorTuitionUpdater::validationRules());

        EducatorTuitionUpdater::apply($educator, $request, $validated);

        return redirect()
            ->route('educator.tuition.edit')
            ->with('status', 'Tuition details saved successfully.');
    }
}
