<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Support\EducatorTuitionUpdater;
use App\Support\ManagedEducator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducatorTuitionController extends Controller
{
    public function edit(Request $request): View
    {
        // (the teacher's own account, also when an admin edits it)
        $educator = ManagedEducator::current($request);
        abort_unless($educator, 404);
        $user = ManagedEducator::owner($request);

        return view('backend.educator.tuition', [
            'user' => $user,
            'educator' => $educator,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $educator = ManagedEducator::current($request);
        abort_unless($educator, 404);
        $byAdmin = ManagedEducator::fromAdmin($request) !== null;

        $validated = $request->validate(EducatorTuitionUpdater::validationRules());

        EducatorTuitionUpdater::apply($educator, $request, $validated);

        return redirect()
            ->to($byAdmin ? ManagedEducator::adminRoute($educator, 'tuition.edit') : route('educator.tuition.edit'))
            ->with('status', 'Tuition details saved successfully.');
    }
}
