<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Models\InstituteLeaveRule;
use App\Support\InstituteDiaryConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstituteLeaveRuleController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $institute = $request->user()->institute;
        abort_unless($institute, 403);

        $user = $request->user();
        $validated = $this->validateRule($request, $user);
        $validated['sort_order'] = (int) $institute->leaveRules()->max('sort_order') + 1;

        $rule = $institute->leaveRules()->create($validated);

        return response()->json([
            'message' => 'Leave rule added successfully.',
            'item_html' => view('backend.institute.partials.diary-leave-rule-item', [
                'rule' => $rule,
                'audienceLabels' => InstituteDiaryConfig::applicableAudiences($user),
            ])->render(),
            'id' => $rule->id,
        ]);
    }

    public function update(Request $request, InstituteLeaveRule $leaveRule): JsonResponse
    {
        $institute = $request->user()->institute;
        abort_unless($institute && (int) $leaveRule->institute_id === (int) $institute->id, 403);

        $user = $request->user();
        $validated = $this->validateRule($request, $user);
        $leaveRule->update($validated);

        return response()->json([
            'message' => 'Leave rule updated successfully.',
            'item_html' => view('backend.institute.partials.diary-leave-rule-item', [
                'rule' => $leaveRule,
                'audienceLabels' => InstituteDiaryConfig::applicableAudiences($user),
            ])->render(),
            'id' => $leaveRule->id,
        ]);
    }

    public function destroy(Request $request, InstituteLeaveRule $leaveRule): JsonResponse
    {
        $institute = $request->user()->institute;
        abort_unless($institute && (int) $leaveRule->institute_id === (int) $institute->id, 403);

        $ruleId = $leaveRule->id;
        $leaveRule->delete();

        return response()->json([
            'message' => 'Leave rule removed successfully.',
            'id' => $ruleId,
        ]);
    }

    /** @return array<string, mixed> */
    private function validateRule(Request $request, \App\Models\User $user): array
    {
        $leaveTypes = array_keys(InstituteDiaryConfig::leaveTypes());
        $audiences = array_keys(InstituteDiaryConfig::applicableAudiences($user));

        $validated = $request->validate([
            'leave_type' => ['required', 'string', Rule::in($leaveTypes)],
            'allowed_days' => ['required', 'integer', 'min:0', 'max:366'],
            'applicable_to' => ['nullable', 'array'],
            'applicable_to.*' => ['string', Rule::in($audiences)],
            'is_paid' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_paid'] = $request->boolean('is_paid', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['applicable_to'] = array_values($validated['applicable_to'] ?? []);

        return $validated;
    }
}
