<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstituteReview;
use App\Support\ManagedInstitute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** The admin removing a visitor's review of the school being edited (the school itself can't) */
class InstituteReviewController extends Controller
{
    public function destroy(Request $request, InstituteReview $review): JsonResponse
    {
        $institute = ManagedInstitute::fromAdmin($request);
        abort_unless($institute && (int) $review->institute_id === (int) $institute->id, 404);

        $review->delete();

        return response()->json(['message' => 'Review removed.', 'id' => $review->id]);
    }
}
