<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\ProfileReport;
use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileReportController extends Controller
{
    public function consultant(Request $request, Consultant $consultant): JsonResponse
    {
        abort_unless($consultant->isApproved(), 404);

        return $this->store($request, $consultant, 'Consultant');
    }

    public function serviceProvider(Request $request, ServiceProvider $service_provider): JsonResponse
    {
        abort_unless($service_provider->isApproved(), 404);

        return $this->store($request, $service_provider, 'Service provider');
    }

    private function store(Request $request, Model $reportable, string $label): JsonResponse
    {
        abort_if((int) $request->user()->id === (int) $reportable->user_id, 403, 'You cannot report your own profile.');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        ProfileReport::create([
            'reportable_type' => $reportable::class,
            'reportable_id' => $reportable->getKey(),
            'reported_by' => $request->user()->id,
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => $label.' reported successfully.',
        ]);
    }
}
