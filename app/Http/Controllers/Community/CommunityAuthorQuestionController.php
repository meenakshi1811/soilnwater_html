<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Mail\CommunityAuthorQuestionAnsweredMail;
use App\Mail\CommunityAuthorQuestionReceivedMail;
use App\Models\CommunityAuthorQuestion;
use App\Models\CommunityPost;
use App\Models\User;
use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommunityAuthorQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $author = $request->user();
        $status = $request->string('status')->toString();

        $questionsQuery = CommunityAuthorQuestion::query()
            ->forAuthor($author->id)
            ->with(['asker:id,name,full_name,email', 'post:id,title,slug'])
            ->latest();

        $pendingQuestions = (clone $questionsQuery)->pending()->get();
        $answeredQuestions = (clone $questionsQuery)->answered()->get();

        $questions = match ($status) {
            'answered' => $answeredQuestions,
            'pending' => $pendingQuestions,
            default => $pendingQuestions->concat($answeredQuestions),
        };

        return view('backend.community-author-questions.index', [
            'questions' => $questions,
            'pendingCount' => $pendingQuestions->count(),
            'answeredCount' => $answeredQuestions->count(),
            'activeStatus' => $status ?: 'all',
        ]);
    }

    public function storeForPost(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->user_id, 422, 'This post does not have an author to contact.');

        return $this->storeQuestion($request, $post->user, $post);
    }

    public function storeForAuthor(Request $request, User $author): JsonResponse|RedirectResponse
    {
        return $this->storeQuestion($request, $author);
    }

    public function answer(Request $request, CommunityAuthorQuestion $question): JsonResponse|RedirectResponse
    {
        abort_unless($question->author_id === $request->user()->id, 403);

        $data = $request->validate([
            'answer' => ['required', 'string', 'max:3000'],
        ]);

        $question->update([
            'answer' => $data['answer'],
            'answered_at' => now(),
        ]);

        $question->load(['author', 'asker', 'post']);

        $this->notifyAskerOfAnswer($question);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your answer has been published for the reader.',
            ]);
        }

        return back()->with('success', 'Your answer has been published for the reader.');
    }

    private function storeQuestion(Request $request, User $author, ?CommunityPost $post = null): JsonResponse|RedirectResponse
    {
        abort_if($request->user()->id === $author->id, 422, 'You cannot ask a question to yourself.');

        $data = $request->validate([
            'question' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $question = CommunityAuthorQuestion::query()->create([
            'author_id' => $author->id,
            'asked_by' => $request->user()->id,
            'community_post_id' => $post?->id,
            'question' => $data['question'],
        ]);

        $question->load(['author', 'asker', 'post']);

        $this->notifyAuthorOfQuestion($question);

        $message = 'Your question has been sent to the author. You will be notified when it is answered.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    private function notifyAuthorOfQuestion(CommunityAuthorQuestion $question): void
    {
        $askerName = $question->askerDisplayName();

        PortalNotificationService::notifyUser(
            $question->author,
            'New question from a reader',
            $askerName.' asked: '.Str::limit($question->question, 120),
            route('community.author-questions.index'),
            'community'
        );

        $recipient = $question->author?->email;
        if ($recipient) {
            Mail::to($recipient)->send(new CommunityAuthorQuestionReceivedMail($question));
        }
    }

    private function notifyAskerOfAnswer(CommunityAuthorQuestion $question): void
    {
        $authorName = $question->authorDisplayName();

        PortalNotificationService::notifyUser(
            $question->asker,
            'Your question was answered',
            $authorName.' answered your question on SoilnWater Community.',
            $question->publicUrl(),
            'community'
        );

        $recipient = $question->asker?->email;
        if ($recipient) {
            Mail::to($recipient)->send(new CommunityAuthorQuestionAnsweredMail($question));
        }
    }
}
