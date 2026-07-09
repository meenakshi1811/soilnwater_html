<?php

namespace App\Http\Controllers\Discussion;

use App\Events\Discussion\ReactionUpdated;
use App\Http\Controllers\Controller;
use App\Models\DiscussionReaction;
use App\Models\DiscussionReply;
use App\Models\DiscussionTopic;
use App\Support\DiscussionReactions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscussionReactionController extends Controller
{
    public function reactToTopic(Request $request, DiscussionTopic $topic): JsonResponse|RedirectResponse
    {
        return $this->toggleReaction($request, $topic);
    }

    public function reactToReply(Request $request, DiscussionReply $reply): JsonResponse|RedirectResponse
    {
        return $this->toggleReaction($request, $reply);
    }

    private function toggleReaction(Request $request, Model $reactable): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'reaction' => ['required', 'string', Rule::in(DiscussionReactions::labels())],
        ]);

        $existing = DiscussionReaction::query()->where([
            'reactable_type' => $reactable->getMorphClass(),
            'reactable_id' => $reactable->getKey(),
            'user_id' => $request->user()->id,
            'reaction' => $data['reaction'],
        ])->first();

        if ($existing) {
            $existing->delete();
            $message = 'Reaction removed.';
            $active = false;
        } else {
            DiscussionReaction::query()->create([
                'reactable_type' => $reactable->getMorphClass(),
                'reactable_id' => $reactable->getKey(),
                'user_id' => $request->user()->id,
                'reaction' => $data['reaction'],
            ]);
            $message = 'Reaction added.';
            $active = true;
        }

        $counts = $reactable->reactions()
            ->selectRaw('reaction, count(*) as total')
            ->groupBy('reaction')
            ->pluck('total', 'reaction')
            ->all();

        ReactionUpdated::dispatch($reactable, $data['reaction'], $active, $counts);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'reaction' => $data['reaction'],
                'active' => $active,
                'counts' => $counts,
            ]);
        }

        return back()->with('success', $message);
    }
}
